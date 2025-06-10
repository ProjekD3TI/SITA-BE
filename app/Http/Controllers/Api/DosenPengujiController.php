<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use App\Models\Dosen;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;
use App\Http\Resources\DosenPengujiResource;

class DosenPengujiController extends Controller
{
    public function getAllDosen()
    {
        try {
            // Impor model Dosen jika belum diimpor
            // Pastikan untuk menambahkan "use App\Models\Dosen;" di bagian atas file
            $dosen = Dosen::select('id', 'name', 'nik')
                ->orderBy('name')
                ->get();

            return ApiResponse::success($dosen, 'Data dosen berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Data dosen gagal diambil: ' . $e->getMessage(), 500);
        }
    }
    public function getReadyTAForAdmin()
    {
        try {
            // Cek list tugas akhir yang sudah memiliki minimal 9 bimbingan yang sudah diverifikasi (bisa lanjut ke semhas & ta)
            $listTugasAkhir = TugasAkhir::withCount([
                'bimbingan' => function ($query) {
                    $query->where('status_verifikasi', 1);
                }
            ])
                ->having('bimbingan_count', '>=', 9)
                ->paginate(10);

            return ApiResponse::success(TugasAkhirResource::collection($listTugasAkhir));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function storeForAdmin(Request $request)
    {
        try {
            $request->validate([
                'list_dosen' => 'required|array|size:3',
                'list_dosen.*.dosen_id' => 'required|exists:dosen,id',
                'list_dosen.*.jabatan_penguji' => 'required|in:ketua,sekretaris,penguji',
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
            ], [
                'list_dosen.size' => 'Harus memilih 3 dosen!',
            ]);

            $tugasAkhir = TugasAkhir::findOrFail($request->tugas_akhir_id);

            if (!$tugasAkhir->proposal_id) {
                return ApiResponse::error('Tugas Akhir harus memiliki proposal terlebih dahulu sebelum menambahkan dosen penguji', 400);
            }

            // Cek apakah jumlah dosen penguji lebih dari 3 langsung dari database DosenPenguji agar lebih efisien (tidak perlu melakukan query lagi)
            if (DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)->count() >= 3) {
                return ApiResponse::error('Maksimal 3 dosen penguji untuk tugas akhir ini!', 400);
            }

            $sekretaris = collect($request->list_dosen)
                ->firstWhere('jabatan_penguji', 'sekretaris');
            if (!$sekretaris || $sekretaris['dosen_id'] != $tugasAkhir->dosen_pembimbing_id) {
                return ApiResponse::error('Jabatan sekretaris harus diisi oleh dosen pembimbing.', 400);
            }

            // Memastikan salah satu dosen yang dipilih adalah Dosen Pembimbing
            $listDosen = $request->input('list_dosen', []);
            if (!is_array($listDosen)) {
                return ApiResponse::error('Format list_dosen tidak valid.', 400);
            }
            $dosenIds = array_column($listDosen, 'dosen_id');
            if (count($dosenIds) !== count(array_unique($dosenIds))) {
                return ApiResponse::error('Tidak boleh ada dosen penguji yang sama.', 400);
            }

            // Cek apakah dosen yang dipilih sudah ada sebagai dosen penguji di tugas akhir yang sama
            $cekDosenPenguji = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
                ->whereIn('dosen_id', array_column($request->list_dosen, 'dosen_id'))
                ->exists();

            if ($cekDosenPenguji) {
                return ApiResponse::error('Dosen yang anda pilih sudah ada di tugas akhir ini', 400);
            }

            $dosenPengujiData = [];
            foreach ($request->list_dosen as $dosen) {
                $dosenPengujiData[] = [
                    'dosen_id' => $dosen['dosen_id'],
                    'tugas_akhir_id' => $request->tugas_akhir_id,
                    'jabatan_penguji' => $dosen['jabatan_penguji'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DosenPenguji::insert($dosenPengujiData);

            $tugasAkhir = TugasAkhir::with('dosenPenguji')
                ->findOrFail($request->tugas_akhir_id);

            return ApiResponse::success(
                DosenPengujiResource::collection($tugasAkhir->dosenPenguji),
                'Dosen penguji berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Data plotting penguji gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }

    public function updateForAdmin(Request $request, $tugasAkhirId)
    {
        try {
            $request->validate([
                'list_dosen' => 'required|array|size:3',
                'list_dosen.*.dosen_id' => 'required|exists:dosen,id',
                'list_dosen.*.jabatan_penguji' => 'required|in:ketua,sekretaris,penguji',
            ], [
                'list_dosen.size' => 'Harus memilih 3 dosen!',
            ]);

            $tugasAkhir = TugasAkhir::findOrFail($tugasAkhirId);

            // CEK: Jika mahasiswa sudah lulus (status_kelulusan 1 atau 2), tidak boleh update dosen penguji
            if (in_array($tugasAkhir->status_kelulusan, [1, 2])) {
                return ApiResponse::error('Tidak dapat mengubah dosen penguji karena mahasiswa sudah lulus.', 400);
            }

            if (!$tugasAkhir->proposal_id) {
                return ApiResponse::error('Tugas Akhir harus memiliki proposal terlebih dahulu sebelum menambahkan dosen penguji', 400);
            }

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Pastikan apakah salah satu dosen yang dipilih adalah Dosen Pembimbing
            $listDosen = $request->input('list_dosen', []);
            if (!is_array($listDosen)) {
                return ApiResponse::error('Format list_dosen tidak valid.', 400);
            }
            $dosenIds = array_column($listDosen, 'dosen_id');
            if (count($dosenIds) !== count(array_unique($dosenIds))) {
                return ApiResponse::error('Tidak boleh ada dosen penguji yang sama.', 400);
            }

            $sekretaris = collect($request->list_dosen)
                ->firstWhere('jabatan_penguji', 'sekretaris');
            if (!$sekretaris || $sekretaris['dosen_id'] != $tugasAkhir->dosen_pembimbing_id) {
                return ApiResponse::error('Jabatan sekretaris harus diisi oleh dosen pembimbing.', 400);
            }

            // Hapus semua Dosen Penguji lama yang ada untuk tugas akhir ini
            DosenPenguji::where('tugas_akhir_id', $tugasAkhirId)->delete();

            // Siapkan data untuk Dosen Penguji baru
            $dosenPengujiData = [];
            foreach ($request->list_dosen as $dosen) {
                $dosenPengujiData[] = [
                    'dosen_id' => $dosen['dosen_id'],
                    'tugas_akhir_id' => $tugasAkhirId,
                    'jabatan_penguji' => $dosen['jabatan_penguji'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert the new DosenPenguji records
            DosenPenguji::insert($dosenPengujiData);

            // Retrieve the updated DosenPenguji records with eager loading
            $dosenPenguji = DosenPenguji::with(['tugasAkhir', 'dosen'])->where('tugas_akhir_id', $tugasAkhirId)->get();

            return ApiResponse::success(DosenPengujiResource::collection($dosenPenguji), 'Dosen penguji berhasil diperbarui', 200);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Data plotting penguji gagal diperbarui: ' . $e->getMessage(), 500);
        }
    }
}
