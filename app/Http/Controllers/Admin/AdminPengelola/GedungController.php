<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Gedung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 

class GedungController extends Controller
{
    public function showGedung(Request $request) {
        $query = Gedung::query()
        ->leftJoin('lantai_gedung', 'gedung.id_lantai', '=', 'lantai_gedung.id_lantai')
        ->select(
            'gedung.*',
            'lantai_gedung.nama_lantai'
        );

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_gedung', 'like', "%{$searchTerm}%")
                ->orWhere('lokasi', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        $query->orderBy('created_at', 'desc');
        $gedung = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($gedung, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data gedung',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createGedung(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_gedung' => 'required',
            'lokasi' => 'required',
            // 'id_lantai' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $gedung = Gedung::create([
            'nama_gedung' => $request->nama_gedung,
            'lokasi' => $request->lokasi,
            // 'id_lantai' => $request->id_lantai,
        ]);

        return response()->json(['message' => 'gedung berhasil ditambahkan']);

    }

    public function findGedung($id) {
        $gedung = Gedung::find($id);
        if ($gedung) {
            return response()->json($gedung);
        } else {
            return response()->json(['message' => 'gedung tidak ditemukan'], 404);
        }
    }

    public function editGedung(Request $request, $id) {
        
            $db_gedung = Gedung::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_gedung' => 'required',
                'lokasi' => 'required',
                // 'id_lantai' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'nama_gedung' => $request->nama_gedung,
                'lokasi' => $request->lokasi,
                // 'id_lantai' => $request->id_lantai,
            ];

            $db_gedung->update($data);

            return response()->json(['message' => 'gedung berhasil diupdate'], 200);
    }

    public function deleteGedung($id) {
        $gedung = Gedung::findOrFail($id);

        if (!$gedung) {
            return response()->json(['message' => 'gedung tidak ditemukan'], 404);
        }

        $gedung->delete();

        return response()->json(['message' => 'gedung berhasil dihapus']);
    }
}
