<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SemesterResource;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Validation\ValidationException;

class SemesterController extends Controller
{
    public function index()
    {
        try {
            // Option 1: Gunakan select untuk memilih kolom yang diinginkan saja
            // $data = Semester::select('id', 'nama_semester', 'is_default')->get();

            // Option 2: Atau bisa juga gunakan map pada hasil query
            $data = Semester::all()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_semester' => $item->nama_semester,
                    'is_default' => $item->is_default,
                ];
            });

            // Gunakan Resource jika ingin kontrol lebih lanjut
            // $data = SemesterResource::collection($semester);

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

    public function setDefault($id)
    {
        try {

            // Reset dulu semua Semester menjadi non-default
            Semester::query()->update(['is_default' => 0]);

            // Set Semester yang dipilih menjadi default
            $tahunAjaran = Semester::findOrFail($id);
            $tahunAjaran->update(['is_default' => 1]);

            return ApiResponse::success(
                new SemesterResource($tahunAjaran),
                'Semester default berhasil diubah',
                201
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengganti semester default: ' . $e->getMessage(), 500);
        }
    }

}