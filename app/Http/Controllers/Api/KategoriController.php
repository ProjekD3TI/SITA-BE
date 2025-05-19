<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriResource;
use App\Http\Responses\ApiResponse;
use App\Models\Kategori;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function lihatKategoribyMahasiswa()
    {
        try {
            $kategori = Kategori::all();
            $data = KategoriResource::collection($kategori);
            return ApiResponse::success($data, "Data berhasil diambil");
        } catch (\Exception $e) {
            //throw $th;
            return ApiResponse::error("Gagal mengambil data" . $e->getMessage(), 500);
        }
    }

    public function tambahKategoriByAdmin(Request $request)
    {
        try {
            $validated = $request->validate(['nama_kategori' => 'required|string|max:100']);
            $kategori = Kategori::create($validated);
            return ApiResponse::success($kategori, 'Kategori berhasil ditambahkan', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('validasi gagal' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Kategori gagal ditambahkan: ' . $e->getMessage(), 500);
        }

    }

    public function editKategoriByAdmin(Request $request, $id)
    {
        try {
            $validateData = $request->validate([
                'nama_kategori' => 'required|string'
            ]);
            $kategori = Kategori::findOrFail($id);

            $kategori->nama_kategori = $validateData['nama_kategori'];
            $kategori->save();
            return ApiResponse::success($kategori, 'Kategori berhasil diperbarui', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Kategori gagal diperbarui: ' . $e->getMessage(), 500);
        }
    }
}