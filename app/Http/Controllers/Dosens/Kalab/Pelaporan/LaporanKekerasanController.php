<?php

namespace App\Http\Controllers\Dosens\Kalab\Pelaporan;

use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pelaporan\LaporanKekerasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class LaporanKekerasanController extends Controller
{
    public function showAllLaporanKekerasan()
    {
        $laporans = LaporanKekerasan::all(); 
        return response()->json($laporans);
    }

    public function createLaporanKekerasan(Request $request)
    {
        // First, validate reCAPTCHA
        $recaptchaResponse = $request->input('g-recaptcha-response');
        
        if (!$this->validateRecaptcha($recaptchaResponse)) {
            return response()->json([
                'message' => 'reCAPTCHA verification failed',
                'errors' => ['recaptcha' => ['Verifikasi reCAPTCHA gagal. Silakan coba lagi.']]
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'image_path' => 'required|array', 
            'image_path.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Increased max size to 5MB
            'category_id' => 'required',
            'deskripsi' => 'required',
            'tanggal_kejadian' => 'required',
            'nomor_telepon' => 'required',
            'nama_pelapor' => 'required',
            'nim_pelapor' => 'required',
            'lampiran_link' => 'nullable|url',
            'agreement' => 'required|accepted',
            
            // Validations for bukti_pelanggaran
            'bukti_pelanggaran' => 'nullable|array',
            'bukti_pelanggaran.*' => 'string',
            
            // Validations for terlapor array
            'terlapor' => 'nullable|array',
            'terlapor.*.nama_lengkap' => 'required_with:terlapor|string|max:255',
            'terlapor.*.email' => 'nullable|email|max:255',
            'terlapor.*.nomor_telepon' => 'nullable|string|max:20',
            'terlapor.*.status_warga' => 'required_with:terlapor|in:dosen,mahasiswa,staff,lainnya',
            'terlapor.*.jenis_kelamin' => 'required_with:terlapor|in:laki-laki,perempuan',
            'terlapor.*.unit_kerja' => 'nullable|string|max:255',
            'terlapor.*.umur_terlapor' => 'nullable|in:<20,20-40,40<',
            
            // Validations for saksi array
            'saksi' => 'nullable|array',
            'saksi.*.nama_lengkap' => 'required_with:saksi|string|max:255',
            'saksi.*.email' => 'required_with:saksi|email|max:255',
            'saksi.*.nomor_telepon' => 'required_with:saksi|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'deskripsi' => $request->deskripsi,
            'tanggal_kejadian' => $request->tanggal_kejadian,
            'nomor_telepon' => $request->nomor_telepon,
            'nomor_laporan_kekerasan' => LaporanKekerasan::generateNomorLaporanKekerasan(),
            'nama_pelapor' => $request->nama_pelapor,
            'nim_pelapor' => $request->nim_pelapor,
            'status' => 'unverified',
            'bukti_pelanggaran' => $request->bukti_pelanggaran,
            'terlapor' => $request->terlapor,
            'saksi' => $request->saksi,
            'lampiran_link' => $request->lampiran_link,
            'created_at' => now(),
            'updated_at' => now(),
            'username' => $request->username ?? 'miftahul01',
        ];

        if ($request->hasFile('image_path')) {
            $imageNames = [];
            
            // Process multiple images
            foreach($request->file('image_path') as $image) {
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/laporankekerasan', $filename);
                
                $imageNames[] = $filename;
            }
            
            // Store image names as JSON in the database
            $data['image_path'] = json_encode($imageNames);
    
            $laporan = LaporanKekerasan::create($data);

            // Send email notification to Kalab staff
            $this->sendEmailNotification($laporan);
            
            return response()->json([
                'message' => 'Laporan kekerasan berhasil ditambahkan',
                'laporan' => $laporan
            ], 201);
        }
    
        return response()->json(['message' => 'Image upload failed'], 400);
    }
    
    /**
     * Validate Google reCAPTCHA v2 response
     * 
     * @param string $recaptchaResponse
     * @return bool
     */
    private function validateRecaptcha($recaptchaResponse) 
    {
        if (empty($recaptchaResponse)) {
            \Log::warning('Empty reCAPTCHA response received');
            return false;
        }
        
        // Use your reCAPTCHA v2 secret key here
        $recaptchaSecretKey = env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
        
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecretKey,
                'response' => $recaptchaResponse,
                'remoteip' => request()->ip()
            ]);
            
            $body = $response->json();
            \Log::info('reCAPTCHA response: ' . json_encode($body));
            
            // For v2, we only need to check the success parameter
            if (!isset($body['success']) || !$body['success']) {
                \Log::warning('reCAPTCHA verification failed: ' . json_encode($body));
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email notification to Kalab staff
     * 
     * @param \App\Models\Pelaporan\LaporanKekerasan $laporan
     * @return void
     */
    private function sendEmailNotification($laporan)
    {
        try {
            $kalabJabatan = Jabatan::where('name', 'Kalab')->first();
            if ($kalabJabatan) {
                $kalabDosens = Dosen::whereHas('jabatans', function($query) use ($kalabJabatan) {
                    $query->where('jabatan_id', $kalabJabatan->id);
                })->pluck('email')->toArray();

                if (!empty($kalabDosens)) {
                    Mail::send('pelaporan.send-pelapor-kekerasan', [
                        'nomor_laporan_kekerasan' => $laporan->nomor_laporan_kekerasan,
                        'nama_pelapor' => $laporan->nama_pelapor,
                        'tanggal_kejadian' => $laporan->tanggal_kejadian,
                    ], function($message) use ($kalabDosens) {
                        $message->to($kalabDosens);
                        $message->subject('Laporan Kekerasan Portal D3TI');
                    });
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    public function showDetailLaporanKekerasan($id)
    {
        $laporan = LaporanKekerasan::find($id);
        if ($laporan) {
            // Ensure JSON fields are properly formatted as arrays
            if (isset($laporan->image_path) && is_string($laporan->image_path)) {
                $laporan->image_path = json_decode($laporan->image_path, true) ?: [];
            }
            
            if (empty($laporan->terlapor)) {
                $laporan->terlapor = [];
            } elseif (is_string($laporan->terlapor)) {
                $laporan->terlapor = json_decode($laporan->terlapor, true) ?: [];
            }
            
            if (empty($laporan->bukti_pelanggaran)) {
                $laporan->bukti_pelanggaran = [];
            } elseif (is_string($laporan->bukti_pelanggaran)) {
                $laporan->bukti_pelanggaran = json_decode($laporan->bukti_pelanggaran, true) ?: [];
            }
            
            if (empty($laporan->saksi)) {
                $laporan->saksi = [];
            } elseif (is_string($laporan->saksi)) {
                $laporan->saksi = json_decode($laporan->saksi, true) ?: [];
            }
            
            return response()->json($laporan);
        } else {
            return response()->json(['message' => 'laporan kekerasan tidak ditemukan'], 404);
        }
    }

    public function updateLaporanKekerasan(Request $request, $id)
    {
        $laporan = LaporanKekerasan::find($id);
        if (!$laporan) {
            return response()->json(['message' => 'laporan kekerasan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required',
            'category_id' => 'sometimes|required',
            'deskripsi' => 'sometimes|required',
            'tanggal_kejadian' => 'sometimes|required',
            'nomor_telepon' => 'sometimes|required',
            'nama_pelapor' => 'sometimes|required',
            'nim_pelapor' => 'sometimes|required',
            'lampiran_link' => 'nullable|url',
            
            // Validations for bukti_pelanggaran
            'bukti_pelanggaran' => 'nullable|array',
            'bukti_pelanggaran.*' => 'string',
            
            // Validations for terlapor
            'terlapor' => 'nullable|array',
            'terlapor.*.nama_lengkap' => 'required_with:terlapor|string|max:255',
            'terlapor.*.email' => 'nullable|email|max:255',
            'terlapor.*.nomor_telepon' => 'nullable|string|max:20',
            'terlapor.*.status_warga' => 'required_with:terlapor|in:dosen,mahasiswa,staff,lainnya',
            'terlapor.*.jenis_kelamin' => 'required_with:terlapor|in:laki-laki,perempuan',
            'terlapor.*.unit_kerja' => 'nullable|string|max:255',
            'terlapor.*.umur_terlapor' => 'nullable|in:<20,20-40,40<',
            
            // Validations for saksi
            'saksi' => 'nullable|array',
            'saksi.*.nama_lengkap' => 'required_with:saksi|string|max:255',
            'saksi.*.email' => 'required_with:saksi|email|max:255',
            'saksi.*.nomor_telepon' => 'required_with:saksi|string|max:20',
            
            // Validations for new images
            'image_path' => 'nullable|array',
            'image_path.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update basic fields
        if ($request->has('judul')) $laporan->judul = $request->judul;
        if ($request->has('category_id')) $laporan->category_id = $request->category_id;
        if ($request->has('deskripsi')) $laporan->deskripsi = $request->deskripsi;
        if ($request->has('tanggal_kejadian')) $laporan->tanggal_kejadian = $request->tanggal_kejadian;
        if ($request->has('nomor_telepon')) $laporan->nomor_telepon = $request->nomor_telepon;
        if ($request->has('nama_pelapor')) $laporan->nama_pelapor = $request->nama_pelapor;
        if ($request->has('nim_pelapor')) $laporan->nim_pelapor = $request->nim_pelapor;
        if ($request->has('lampiran_link')) $laporan->lampiran_link = $request->lampiran_link;
        
        // Update bukti_pelanggaran, terlapor and saksi fields
        if ($request->has('bukti_pelanggaran')) $laporan->bukti_pelanggaran = $request->bukti_pelanggaran;
        if ($request->has('terlapor')) $laporan->terlapor = $request->terlapor;
        if ($request->has('saksi')) $laporan->saksi = $request->saksi;
        
        // Update username and timestamps
        $laporan->updated_at = now();
        if ($request->has('username')) $laporan->username = $request->username;

        // Update images if provided
        if ($request->hasFile('image_path')) {
            // Get existing images
            $existingImages = [];
            if ($laporan->image_path) {
                $existingImages = json_decode($laporan->image_path, true) ?: [];
            }
            
            // Process new images
            foreach($request->file('image_path') as $image) {
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/laporankekerasan', $filename);
                
                $existingImages[] = $filename;
            }
            
            // Store updated image names
            $laporan->image_path = json_encode($existingImages);
        }

        $laporan->save();
        
        return response()->json([
            'message' => 'Laporan kekerasan berhasil diperbarui',
            'laporan' => $laporan
        ]);
    }

    public function deleteLaporanKekerasan($id)
    {
        $laporan = LaporanKekerasan::find($id);

        if (!$laporan) {
            return response()->json(['message' => 'laporan Kekerasan tidak ditemukan'], 404);
        }
        
        // Delete associated images
        if ($laporan->image_path) {
            $images = json_decode($laporan->image_path, true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::delete('public/laporankekerasan/' . $image);
                }
            }
        }
    
        $laporan->delete();
    
        return response()->json(['message' => 'laporan Kekerasan berhasil dihapus']);
    }
    
    public function add_laporan(Request $request)
    {
        // This is an alias for createLaporanKekerasan to match the Vue.js frontend
        return $this->createLaporanKekerasan($request);
    }
}