<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TahunAjaranResource;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\TahunAjaran;
use Illuminate\Validation\ValidationException;

class TahunAjaranController extends Controller
{
    public function index()
    {
        try {
            $tahunAjaran = TahunAjaran::all();
            $data = TahunAjaranResource::collection($tahunAjaran);

            return ApiResponse::success($data, 'Data tahun ajaran berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            return ApiResponse::success(
                new TahunAjaranResource($tahunAjaran),
                "Detail tahun ajaran berhasil diambil"
            );
        } catch (\Exception $e) {
            return ApiResponse::error("Data tahun ajaran tidak ditemukan " . $e->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tahun_ajaran' => 'required|string|max:10',
                'is_default' => 'required|boolean',
            ]);

            $tahunAjaran = TahunAjaran::create($validated);
            return ApiResponse::success(
                new TahunAjaranResource($tahunAjaran),
                'Tahun ajaran berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data tahun ajaran gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            $validated = $request->validate(rules: [
                'tahun_ajaran' => 'required|string|max:10'
            ]);

            $tahunAjaran->update($validated);
            return ApiResponse::success(
                new TahunAjaranResource($tahunAjaran),
                'Data tahun ajaran berhasil diubah',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data tahun ajaran gagal diubah ' . $e->getMessage(), 500);
        }
    }

    public function setDefault($id)
    {
        try {

            // Reset dulu semua tahun ajaran menjadi non-default
            TahunAjaran::query()->update(['is_default' => 0]);

            // Set tahun ajaran yang dipilih menjadi default
            $tahunAjaran = TahunAjaran::findOrFail($id);
            $tahunAjaran->update(['is_default' => 1]);

            return ApiResponse::success(
                new TahunAjaranResource($tahunAjaran),
                'Tahun ajaran default berhasil diubah',
                201
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengganti tahun ajaran default: ' . $e->getMessage(), 500);
        }
    }
    
    public function getDefault(){
        try {
            // Ambil data tahun ajaran aktif
            $tahunAjaran = TahunAjaran::where('is_default', 1)
                ->value('tahun_ajaran');
            
            if (!$tahunAjaran) {
                return ApiResponse::success([
                    'tahun_ajaran' => null
                ], 'Belum ada tahun ajaran yang aktif');
            }
            
            // Return hanya string tahun ajaran
            return ApiResponse::success([
                'tahun_ajaran' => $tahunAjaran
            ], 'Data tahun ajaran berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data tahun ajaran gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            $tahunAjaran->delete();

            return ApiResponse::success(
                null,
                'Data tahun ajaran berhasil dihapus'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Data tahun ajaran gagal dihapus ' . $e->getMessage(), 500);
        }
    }
}
