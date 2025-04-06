<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\LantaiGedung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 

class LantaiController extends Controller
{
    public function showLantai(Request $request) {
        $query = LantaiGedung::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_lantai', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        $query->orderBy('created_at', 'desc');
        $lantai = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($lantai, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data lantai',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createLantai(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_lantai' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $lantai = LantaiGedung::create([
            'nama_lantai' => $request->nama_lantai,
        ]);

        return response()->json(['message' => 'lantai berhasil ditambahkan']);

    }

    public function findLantai($id) {
        $lantai = LantaiGedung::find($id);
        if ($lantai) {
            return response()->json($lantai);
        } else {
            return response()->json(['message' => 'lantai tidak ditemukan'], 404);
        }
    }

    public function editLantai(Request $request, $id) {
        
            $db_lantai = LantaiGedung::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_lantai' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'nama_lantai' => $request->nama_lantai,
            ];

            $db_lantai->update($data);

            return response()->json(['message' => 'lantai berhasil diupdate'], 200);
    }

    public function deleteLantai($id) {
        $lantai = LantaiGedung::findOrFail($id);

        if (!$lantai) {
            return response()->json(['message' => 'lantai tidak ditemukan'], 404);
        }

        $lantai->delete();

        return response()->json(['message' => 'lantai berhasil dihapus']);
    }
}