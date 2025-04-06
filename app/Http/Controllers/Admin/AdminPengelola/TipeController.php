<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Models\AdminPengelola\Tipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TipeController extends Controller
{

    public function showTipe(Request $request) {
        $query = Tipe::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_tipe', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        $query->orderBy('created_at', 'desc');
        $tipe = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($tipe, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data tipe',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createTipe(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama_tipe' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $tipe = Tipe::create([
            'nama_tipe' => $request->nama_tipe,
        ]);

        return response()->json(['message' => 'tipe berhasil ditambahkan']);
    }

    public function findTipe($id) {
        $tipe = Tipe::find($id);
        if ($tipe) {
            return response()->json($tipe);
        } else {
            return response()->json(['message' => 'tipe tidak ditemukan'], 404);
        }
    }

    public function editTipe(Request $request, $id) {
        $db_tipe = Tipe::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_tipe' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'nama_tipe' => $request->nama_tipe,
        ];

        $db_tipe->update($data);

        return response()->json(['message' => 'tipe berhasil diupdate'], 200);
    }

    public function deleteTipe($id) {
        $tipe = Tipe::findOrFail($id);

        if (!$tipe) {
            return response()->json(['message' => 'tipe tidak ditemukan'], 404);
        }

        $tipe->delete();

        return response()->json(['message' => 'tipe berhasil dihapus']);
    }
}