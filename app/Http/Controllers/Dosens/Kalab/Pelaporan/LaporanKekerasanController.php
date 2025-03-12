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
