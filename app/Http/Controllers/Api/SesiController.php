<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SesiResource;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Sesi;
use Illuminate\Validation\ValidationException;
class SesiController extends Controller
{

    // ========================== FITUR ADMIN ==========================

    public function index(Request $request)
    {
        try {
            // Ambil parameter search dan pagination dari request
            $search = $request->query('search');

            // Query dasar
            $query = Sesi::query();

            // Jika ada search, filter berdasarkan nama_sesi
            if ($search) {
                $query->where('nama_sesi', 'like', '%' . $search . '%');
            }

            // Paginate hasil
            $sesi = $query->orderBy('id', 'desc')->paginate(10);

            // Gunakan resource collection
            $data = SesiResource::collection($sesi);

            // Tambahkan meta pagination ke response
            return ApiResponse::success([
                'data' => $data,
                'pagination' => [
                    'current_page' => $sesi->currentPage(),
                    'last_page' => $sesi->lastPage(),
                    'per_page' => $sesi->perPage(),
                    'total' => $sesi->total(),
                ]
            ], 'Data Sesi Berhasil Diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $sesi = Sesi::findOrFail($id);
            return ApiResponse::success(
                new SesiResource($sesi),
                'Detail sesi berhasil diambil'
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Data sesi tidak ditemukan ' . $e->getMessage(), 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_sesi' => 'required|string|max:50',
                'waktu_mulai' => 'required',
                'waktu_selesai' => 'required|after:waktu_mulai',
            ]);
            $sesi = Sesi::create($validated);
            return ApiResponse::success(
                new SesiResource($sesi),
                'Sesi berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data sesi gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $sesi = Sesi::findOrFail($id);
            $validated = $request->validate([
                'nama_sesi' => 'required|string|max:50',
                'waktu_mulai' => 'required',
                'waktu_selesai' => 'required|after:waktu_mulai',
            ]);

            $sesi->update($validated);
            return ApiResponse::success(
                new SesiResource($sesi),
                'Data sesi berhasil diubah',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data sesi gagal diubah ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        // try {
        //     $sesi = Sesi::findOrFail($id);
        //     $sesi->delete();

        //     return ApiResponse::success(
        //         null,
        //         'Data sesi berhasil dihapus'
        //     );
        // } catch (\Exception $e) {
        //     return ApiResponse::error('Data sesi gagal dihapus ' . $e->getMessage(), 500);
        // }
    }

    // ========================== END FITUR ADMIN ==========================



    // ========================== FITUR DOSEN ==========================

    public function lihatSesiForDosen()
    {
        try {
            $sesi = Sesi::all();
            // Format the data as requested
            $data = $sesi->map(function ($item) {
                return [
                    'sesi_id' => $item->id,
                    'nama_sesi' => $item->nama_sesi,
                    'waktu_mulai' => date('H:i', strtotime($item->waktu_mulai)),
                    'waktu_selesai' => date('H:i', strtotime($item->waktu_selesai)),
                ];
            });
            return ApiResponse::success($data, "Data sesi berhasil diambil");
        } catch (\Exception $e) {
            return ApiResponse::error("Gagal mengambil data sesi: " . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DOSEN ==========================

}
