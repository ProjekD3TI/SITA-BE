<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RuangResource;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Ruang;
use Illuminate\Validation\ValidationException;

class RuangController extends Controller
{
    // ========================== FITUR ADMIN ==========================

    public function index(Request $request)
    {
        try {
            // Ambil parameter search dan pagination dari request
            $search = $request->query('search');

            // Query dasar
            $query = Ruang::query();

            // Jika ada search, filter berdasarkan nama_ruang
            if ($search) {
                $query->where('nama_ruang', 'like', '%' . $search . '%');
            }

            // Paginate hasil
            $ruang = $query->orderBy('id', 'desc')->paginate(10);

            // Gunakan resource collection
            $data = RuangResource::collection($ruang);

            // Tambahkan meta pagination ke response
            return ApiResponse::success([
                'data' => $data,
                'pagination' => [
                    'current_page' => $ruang->currentPage(),
                    'last_page' => $ruang->lastPage(),
                    'per_page' => $ruang->perPage(),
                    'total' => $ruang->total(),
                ]
            ], 'Data ruang berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $ruang = Ruang::findOrFail($id);
            return ApiResponse::success(new RuangResource($ruang), 'Detail ruang berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data ruang tidak ditemukan' . $e->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_ruang' => 'required|string|max:50',
            ]);
            $ruang = Ruang::create($validated);
            return ApiResponse::success(
                new RuangResource($ruang),
                'Data ruang berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ruang gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ruang = Ruang::findOrFail($id);
            $validated = $request->validate([
                'nama_ruang' => 'required|string|max:50',
            ]);
            $ruang->update($validated);
            return ApiResponse::success(
                new RuangResource($ruang),
                'Data ruang berhasil diubah',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ruang gagal diubah ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        // try {
        //     $ruang = Ruang::findOrFail($id);
        //     $ruang->delete();

        //     return ApiResponse::success(null, "Data sesi berhasil ditambahkan");
        // } catch (\Exception $e) {
        //     return ApiResponse::error(' Sesi gagal ditambahkan ' . $e->getMessage(), 500);
        // }
    }

    // ========================== END FITUR ADMIN ==========================

}