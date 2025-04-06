<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Merk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MerkController extends Controller

{
    public function showMerk(Request $request) {
        $query = Merk::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_merk', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        $query->orderBy('created_at', 'desc');
        $merk = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($merk, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Merk',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createMerk(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_merk' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $merk = Merk::create([
            'nama_merk' => $request->nama_merk,
        ]);

        return response()->json(['message' => 'merk berhasil ditambahkan']);

    }

    public function findMerk($id) {
        $merk = Merk::find($id);
        if ($merk) {
            return response()->json($merk);
        } else {
            return response()->json(['message' => 'merk tidak ditemukan'], 404);
        }
    }

    public function editMerk(Request $request, $id) {
        
            $db_merk = Merk::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_merk' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'nama_merk' => $request->nama_merk,
            ];

            $db_merk->update($data);

            return response()->json(['message' => 'merk berhasil diupdate'], 200);
    }

        public function deleteMerk($id) {
        $merk = Merk::findOrFail($id);

        if (!$merk) {
            return response()->json(['message' => 'merk tidak ditemukan'], 404);
        }

        $merk->delete();

        return response()->json(['message' => 'merk berhasil dihapus']);
    }
}