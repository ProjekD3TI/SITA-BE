<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\GedungLantai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GedungLantaiController extends Controller
{
    public function getGedungLantai(Request $request) 
    {
        $query = GedungLantai::query();
        $query->select('gedung_lantai.id', 'gedung_lantai.id_lantai', 'gedung_lantai.id_gedung', 
                    'gedung.nama_gedung', 'lantai_gedung.nama_lantai', 'gedung_lantai.created_at')
            ->join('gedung', 'gedung_lantai.id_gedung', '=', 'gedung.id_gedung')
            ->join('lantai_gedung', 'gedung_lantai.id_lantai', '=', 'lantai_gedung.id_lantai');

        if($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('gedung.nama_gedung', 'LIKE', "%{$search}%")
                ->orWhere('lantai_gedung.nama_lantai', 'LIKE', "%{$search}%");
            });
        }
        
        // Specify which table's created_at to use
        $query->orderBy('gedung_lantai.created_at', 'desc');
        $gedung_lantai = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginatedData = PaginationHelper::paginate($gedung_lantai, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Gedung Lantai',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createGedungLantai(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_gedung' => 'required|exists:gedung,id_gedung',
            'id_lantai' => 'required|array|min:1',
            'id_lantai.*' => 'required|distinct|exists:lantai_gedung,id_lantai',  // Validasi setiap id_lantai
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Deteksi apakah data berupa satu atau banyak lantai
        $isBulk = is_array($request->id_lantai);
        
        if ($isBulk) {
            // Bulk insert (multiple lantai untuk satu gedung)
            $insertData = collect($request->id_lantai)->map(function ($lantaiId) use ($request) {
                return [
                    'id_gedung' => $request->id_gedung,
                    'id_lantai' => $lantaiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Optional: hilangkan duplikat sebelum insert
            $insertData = array_filter($insertData, function ($data) {
                return !GedungLantai::where('id_gedung', $data['id_gedung'])
                    ->where('id_lantai', $data['id_lantai'])
                    ->exists();
            });

            GedungLantai::insert($insertData);

            return response()->json([
                'status' => 'success',
                'message' => count($insertData) . ' lantai berhasil ditambahkan ke gedung.',
            ], 201);

        } else {
            // Single insert (satu lantai untuk satu gedung)
            $exists = GedungLantai::where('id_gedung', $request->id_gedung)
                                  ->where('id_lantai', $request->id_lantai)
                                  ->exists();
            
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lantai sudah terdaftar di gedung ini.',
                ], 409);
            }

            GedungLantai::create([
                'id_gedung' => $request->id_gedung,
                'id_lantai' => $request->id_lantai,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Lantai berhasil ditambahkan ke gedung.',
            ], 201);
        }
    }


public function getGedungLantaiById($id)
{
    $gedung_lantai = GedungLantai::find($id); // Pastikan find digunakan dengan ID yang benar
    if (!$gedung_lantai) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gedung Lantai Tidak Ditemukan',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Detail Gedung Lantai',
        'data' => $gedung_lantai,
    ], 200);
}


    // Update GedungLantai by ID
    public function updateGedungLantai(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_gedung' => 'required|exists:gedung,id_gedung',
            'id_lantai' => 'nullable|exists:lantai_gedung,id_lantai',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil data GedungLantai berdasarkan ID
        $gedung_lantai = GedungLantai::findOrFail($id);

        // Perbarui data gedung lantai
        $gedung_lantai->update([
            'id_gedung' => $request->id_gedung,
            'id_lantai' => $request->id_lantai,
        ]);

        // Respons sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Gedung Lantai Berhasil Diperbarui',
            'data' => $gedung_lantai,
        ], 200);
    }
    
    public function deleteGedungLantai($id) {
        $gedung_lantai = GedungLantai::findOrFail($id);
        if (!$gedung_lantai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gedung Lantai Tidak Ditemukan',
            ], 404);
        }

        $gedung_lantai->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Gedung Lantai Berhasil Dihapus',
        ], 200);
    }
}
