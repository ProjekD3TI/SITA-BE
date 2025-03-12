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
            'image_path' => 'required',
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
            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $path = $image->storeAs('public/laporan', $filename);
            
            $data['image_path'] = $filename;
    
            $laporan = Laporan::create($data);

            // Mail::send('pelaporan.send-pelapor', [
            //     'nomor_laporan' =>  $laporan -> nomor_laporan,
            //     'tanggal_kejadian' => $request -> tanggal_kejadian,
            //     'nama_pelapor' => $laporan -> nama_pelapor
            // ], function($message) use ($laporan) {
            //     $message->to(Auth::user()->email, Auth::user()->name);
            //     $message->subject('Laporan Portal D3TI');
            // });

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
            return response()->json($laporan);
        } else {
            return response()->json(['message' => 'laporan tidak ditemukan'], 404);
        }
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
            if ($laporan->image_path) {
                Storage::delete('public/laporan/' . $laporan->image_path);
            }
        
            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $image->storeAs('public/laporan', $filename);
                
            $data['image_path'] = $filename;
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
            Storage::delete('public/laporan/' . $laporan->image_path);
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
