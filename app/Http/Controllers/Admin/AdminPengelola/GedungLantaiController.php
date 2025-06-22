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
        $validator = Validator::make($request->all(), [
            'id_gedung' => 'required',
            'id_lantai' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $gedung_lantai = GedungLantai::create([
            'id_lantai' => $request->id_lantai,
            'id_gedung' => $request->id_gedung,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Gedung Lantai Berhasil Dibuat',
            'data' => $gedung_lantai,
        ], 201);
    }

    public function getGedungLantaiById($id) {
        $gedung_lantai = DB::table('gedung_lantai')
            ->join('gedung', 'gedung_lantai.id_gedung', '=', 'gedung.id')
            ->join('lantai_gedung', 'gedung_lantai.id_lantai', '=', 'lantai_gedung.id')
            ->select('gedung_lantai.id', 'gedung.nama_gedung', 'lantai_gedung.nama_lantai')
            ->where('gedung_lantai.id', $id)
            ->first();

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

    public function updateGedungLantai(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'id_gedung' => 'required|string|max:255',
            'id_lantai' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $gedung_lantai = GedungLantai::findOrFail($id);
        if (!$gedung_lantai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gedung Lantai Tidak Ditemukan',
            ], 404);
        }

        $gedung_lantai->update([
            'id_gedung' => $request->id_gedung,
            'id_lantai' => $request->id_lantai,
        ]);

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
