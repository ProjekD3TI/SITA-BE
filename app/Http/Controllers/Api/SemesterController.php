<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SemesterResource;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;

class SemesterController extends Controller
{
    public function index()
    {
        try {
            $semester = Semester::all();
            $data = SemesterResource::collection($semester);
            return ApiResponse::success($data, 'Data semester berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $semester = Semester::findOrFail($id);
            return ApiResponse::success(new SemesterResource($semester), 'Data semester berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data semester tidak ditemukan ' . $e->getMessage(), 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $semester = Semester::findOrFail($id);
            $validated = $request->validate([
                'nama_semester' => 'required|string',
            ]);
            $semester->update($validated);
            return ApiResponse::success(
                new SemesterResource($semester),
                'Semester berhasil diubah'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Data semester gagal diubah ' . $e->getMessage(), 500);
        }
    }

}
