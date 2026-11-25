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
    public function index(Request $request)
    {
        try {
            $query = TahunAjaran::query();

            // Fitur search (by tahun_ajaran, fleksibel)
            if ($request->has('search') && trim($request->search) !== '') {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    // Case-insensitive, cari tahun_ajaran mengandung string apapun
                    $q->whereRaw('LOWER(tahun_ajaran) LIKE ?', ['%' . strtolower($search) . '%']);
                    // Atau cari tahun_ajaran yang mengandung salah satu tahun (misal "2023" pada "2023/2024")
                    if (preg_match('/\d{4}/', $search, $match)) {
                        $q->orWhere('tahun_ajaran', 'like', '%' . $match[0] . '%');
                    }
                });
            }

            // Pagination (default 10 per page, bisa diubah via ?per_page=...)
            $perPage = $request->get('per_page', 10);
            $tahunAjaran = $query->orderByDesc('id')->paginate($perPage);

            // Replace collection pada paginator agar hanya field yang diinginkan
            $tahunAjaran->setCollection(
                $tahunAjaran->getCollection()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tahun_ajaran' => $item->tahun_ajaran,
                        'is_default' => $item->is_default,
                    ];
                })
            );

            // Ambil info pagination
            $pagination = [
                'current_page' => $tahunAjaran->currentPage(),
                'last_page' => $tahunAjaran->lastPage(),
                'per_page' => $tahunAjaran->perPage(),
                'total' => $tahunAjaran->total(),
            ];
            return ApiResponse::success([
                'data' => $tahunAjaran->items(),
                'pagination' => $pagination,
            ], 'Data tahun ajaran berhasil diambil');
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
                'is_default' => 'boolean', // Hapus required
            ]);

            // Set default value 0 jika is_default tidak disediakan
            if (!isset($validated['is_default'])) {
                $validated['is_default'] = 0;
            }

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

    public function getDefault()
    {
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
