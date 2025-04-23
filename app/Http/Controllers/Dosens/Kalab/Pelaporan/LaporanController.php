<?php

namespace App\Http\Controllers\Dosens\Kalab\Pelaporan;

use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pelaporan\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class LaporanController extends Controller
{
    public function showAllLaporan()
    {
        $laporans = Laporan::all(); 
        return response()->json($laporans);
    }

    public function createLaporan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'image_path.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate multiple images
            'category_id' => 'required',
            'deskripsi' => 'required',
            'tanggal_kejadian' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'deskripsi' => $request->deskripsi,
            'tanggal_kejadian' => $request->tanggal_kejadian,
            'nomor_telepon' => Auth::user()->no_telp,
            'nomor_laporan' => Laporan::generateNomorLaporan(),
            'nama_pelapor' => Auth::user()->name,
            'ni_pelapor' => Auth::user()->nim ?? Auth::user()->nik,
            'status' => 'unverified',
            'email' => Auth::user()->email,
        ];

        if ($request->hasFile('image_path')) {
            $imageNames = [];
            
            // Process multiple images
            foreach($request->file('image_path') as $image) {
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/laporan', $filename);
                
                $imageNames[] = $filename;
            }
            
            // Store image names as JSON in the database
            $data['image_path'] = json_encode($imageNames);
    
            $laporan = Laporan::create($data);

            $kalabJabatan = Jabatan::where('name', 'Kalab')->first();
            if ($kalabJabatan) {
                $kalabDosens = Dosen::whereHas('jabatans', function($query) use ($kalabJabatan) {
                    $query->where('jabatan_id', $kalabJabatan->id);
                })->pluck('email')->toArray();

                if (!empty($kalabDosens)) {
                    Mail::send('pelaporan.send-pelapor', [
                        'nomor_laporan' => $laporan->nomor_laporan,
                        'nama_pelapor' => $laporan->nama_pelapor,
                        'tanggal_kejadian' => $laporan->tanggal_kejadian,
                    ], function($message) use ($kalabDosens) {
                        $message->to($kalabDosens);
                        $message->subject('Laporan Portal D3TI');
                    });
                }
            }
            
            return response()->json([
                'message' => 'Laporan berhasil ditambahkan',
            ], 201);
        }
    
        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function showDetailLaporan($id)
    {
        $laporan = Laporan::find($id);
        if ($laporan) {
            // Decode image paths for the frontend
            if (is_string($laporan->image_path) && $this->isJson($laporan->image_path)) {
                $laporan->image_paths = json_decode($laporan->image_path);
            } else {
                $laporan->image_paths = [$laporan->image_path];
            }
            return response()->json($laporan);
        } else {
            return response()->json(['message' => 'laporan tidak ditemukan'], 404);
        }
    }

    // Helper function to check if a string is valid JSON
    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function editLaporan(Request $request, $id)
    {
        $laporan = Laporan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'category_id' => 'required',
            'deskripsi' => 'required',
            'nomor_telepon' => 'required',
            'nama_pelapor' => 'required',
            'ni_pelapor' => 'required',
            'image_path.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate multiple images
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'deskripsi' => $request->deskripsi,
            'nomor_telepon' => $request->nomor_telepon,
            'nama_pelapor' => $request->nama_pelapor,
            'ni_pelapor' => $request->ni_pelapor,
        ];

        if ($request->hasFile('image_path')) {
            // Delete old images if they exist
            if ($laporan->image_path) {
                $oldImages = json_decode($laporan->image_path, true);
                if (is_array($oldImages)) {
                    foreach ($oldImages as $oldImage) {
                        Storage::delete('public/laporan/' . $oldImage);
                    }
                } else {
                    Storage::delete('public/laporan/' . $laporan->image_path);
                }
            }
            
            $imageNames = [];
            
            // Process multiple images
            foreach($request->file('image_path') as $image) {
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/laporan', $filename);
                
                $imageNames[] = $filename;
            }
            
            // Store image names as JSON in the database
            $data['image_path'] = json_encode($imageNames);
        }
        
        $laporan->update($data);
        
        return response()->json(['message' => 'laporan berhasil diupdate'], 200);
    }

    public function deleteLaporan($id)
    {
        $laporan = Laporan::findOrFail($id);

        if (!$laporan) {
            return response()->json(['message' => 'laporan tidak ditemukan'], 404);
        }

        if ($laporan->image_path) {
            $images = json_decode($laporan->image_path, true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::delete('public/laporan/' . $image);
                }
            } else {
                Storage::delete('public/laporan/' . $laporan->image_path);
            }
        }
    
        $laporan->delete();
    
        return response()->json(['message' => 'laporan berhasil dihapus']);
    }

    public function editStatusLaporan(Request $request, $id)
    {
        $laporan = Laporan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'tanggapan' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = [
            'status' => $request->status,
            'tanggapan' => $request->tanggapan,
        ];
        
        $laporan->update($data);

        Mail::send('pelaporan.send-kalab', [
            'nomor_laporan' => $laporan -> nomor_laporan,
            'nama_pelapor' => $laporan -> nama_pelapor,
            'tanggal_kejadian' => $laporan -> tanggal_kejadian,
            'status' => $request -> status,
            'tanggapan' => $request -> tanggapan
        ], function($message) use ($laporan) {
            $message->to($laporan->email);
            $message->subject('Tanggapan Laporan Portal D3TI');
        });
        
        return response()->json(['message' => 'laporan berhasil diupdate'], 200);
    }
}