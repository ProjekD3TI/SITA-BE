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

class LaporanKekerasanController extends Controller
{
    public function showAllLaporanKekerasan()
    {
        $laporans = LaporanKekerasan::all(); 
        return response()->json($laporans);
    }

    public function createLaporanKekerasan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'image_path' => 'required',
            'category_id' => 'required',
            'deskripsi' => 'required',
            'tanggal_kejadian' => 'required',
            'nomor_telepon' => 'required',
            'nama_pelapor' => 'required',
            'nim_pelapor' => 'required',
            // Frontend will validate these as required, but in backend we allow them to be null
            'saksi_nama' => 'nullable|string',
            'saksi_email' => 'nullable|email',
            'saksi_nomor_telepon' => 'nullable|string',
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
            // Add Saksi fields
            'saksi_nama' => $request->saksi_nama,
            'saksi_email' => $request->saksi_email,
            'saksi_nomor_telepon' => $request->saksi_nomor_telepon,
        ];

        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $path = $image->storeAs('public/laporankekerasan', $filename);
            
            $data['image_path'] = $filename;
    
            $laporan = LaporanKekerasan::create($data);

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
            
            return response()->json([
                'message' => 'Laporan kekerasan berhasil ditambahkan',
                'laporan' => $laporan
            ], 201);
        }
    
        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function showDetailLaporanKekerasan($id)
    {
        $laporan = LaporanKekerasan::find($id);
        if ($laporan) {
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
            'saksi_nama' => 'nullable|string',
            'saksi_email' => 'nullable|email',
            'saksi_nomor_telepon' => 'nullable|string',
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
        
        // Update Saksi fields
        if ($request->has('saksi_nama')) $laporan->saksi_nama = $request->saksi_nama;
        if ($request->has('saksi_email')) $laporan->saksi_email = $request->saksi_email;
        if ($request->has('saksi_nomor_telepon')) $laporan->saksi_nomor_telepon = $request->saksi_nomor_telepon;

        // Update image if provided
        if ($request->hasFile('image_path')) {
            // Remove old image if it exists
            if ($laporan->image_path) {
                Storage::delete('public/laporankekerasan/' . $laporan->image_path);
            }

            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $path = $image->storeAs('public/laporankekerasan', $filename);
            
            $laporan->image_path = $filename;
        }

        $laporan->save();
        
        return response()->json([
            'message' => 'Laporan kekerasan berhasil diperbarui',
            'laporan' => $laporan
        ]);
    }

    public function deleteLaporanKekerasan($id)
    {
        $laporan = LaporanKekerasan::findOrFail($id);

        if (!$laporan) {
            return response()->json(['message' => 'laporan Kekerasan tidak ditemukan'], 404);
        }
    
        $laporan->delete();
    
        return response()->json(['message' => 'laporan Kekerasan berhasil dihapus']);
    }
}