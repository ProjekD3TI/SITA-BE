<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassesController extends Controller
{
    public function getClasses(Request $request)
    {
        $query = Classes::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        $classess = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginatedData = PaginationHelper::paginate($classess, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Kelas',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createClasses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:classes,name',
            'description' => 'nullable',
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan.',
            'name.max' => 'Nama kelas maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $classes = Classes::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas Berhasil Dibuat',
            'data' => $classes,
        ], 201);
    }

    public function getClassesById($id)
    {
        $classes = Classes::find($id);
        if (!$classes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas Tidak Ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail Kelas',
            'data' => $classes,
        ], 200);
    }

    public function updateClasses(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:classes,name,' . $id,
            'description' => 'nullable',
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan.',
            'name.max' => 'Nama kelas maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $classes = Classes::find($id);
        if (!$classes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas Tidak Ditemukan',
            ], 404);
        }

        $classes->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas Berhasil Diperbarui',
            'data' => $classes,
        ], 200);
    }

    public function deleteClasses($id)
    {
        $classes = Classes::find($id);
        if (!$classes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas Tidak Ditemukan',
            ], 404);
        }

        $classes->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas Berhasil Dihapus',
        ], 200);
    }
}
