<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UjianTAResource;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\DosenPenguji;
use App\Models\TugasAkhir;
use App\Models\UjianTA;
use Illuminate\Validation\ValidationException;


class UjianTAController extends Controller
{

    // Menampilkan data tugas akhir yang siap ujian (tidak menampilkan data tugas akhir yang sudah memiliki jadwal ujian)
    public function getTAReadyForAdmin()
    {
        try {
            $calonPesertaUjian = TugasAkhir::with('dosenPenguji')
                ->where('status_aktif', 1)
                ->where('status_valid', 1)
                ->whereDoesntHave('ujianTA')
                ->get();

            return ApiResponse::success($calonPesertaUjian, 'Data tugas akhir yang siap ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data tugas akhir yang siap ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function index()
    {
        try {
            $listUjianTA = UjianTA::with([
                'tugasAkhir',
                'tugasAkhir.mahasiswa',
                'tugasAkhir.dosenPenguji',
                'ruang',
                'sesi',
            ])->paginate(10);

            // \Log::info('Data Ujian TA:', $listUjianTA->toArray());

            return ApiResponse::success(new UjianTAResource($listUjianTA), 'Data ujian TA berhasil diambil');
        } catch (\Exception $e) {
            // \Log::error('Error fetching Ujian TA: ' . $e->getMessage());
            return ApiResponse::error('Data ujian TA gagal diambil: ' . $e->getMessage(), 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
                'tanggal_ujian' => 'required|date',
                'ruang_id' => 'required|exists:ruang,id',
                'sesi_id' => 'required|exists:sesi,id',
                'semester_id' => 'required|exists:semester,id',
                'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                'status_ujian' => 'required|in:aktif,non-aktif'
            ]);

            // Cek apakah tugas akhir sudah memiliki jadwal ujian
            $cekJadwalUjian = UjianTA::where('tugas_akhir_id', $validated['tugas_akhir_id'])->exists();
            if ($cekJadwalUjian) {
                return ApiResponse::error('Tugas akhir sudah memiliki jadwal ujian', 422);
            }

            // Cek apakah ada jadwal bentrok ruangan dan sesinya
            $cekJadwalBentrok = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('ruang_id', $validated['ruang_id'])
                ->where('sesi_id', $validated['sesi_id'])
                ->exists()
            ;

            if ($cekJadwalBentrok) {
                return ApiResponse::error('Ruang dan sesi pada tanggal ini sudah terpakai', 422);
            }

            // Cek apakah ada jadwal bentrok dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($validated['tugas_akhir_id']);
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            $cekBentrokDosen = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('sesi_id', $validated['sesi_id'])
                ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                    $query->whereIn('dosen_id', $listDosenPenguji);
                })
                ->exists();

            if ($cekBentrokDosen) {
                return ApiResponse::error('Dosen penguji sudah ada yang ujian pada sesi ini', 422);
            }

            $ujianTA = UjianTA::create($validated);
            return ApiResponse::success(
                $ujianTA->load(['tugasAkhir', 'tugasAkhir.mahasiswa', 'tugasAkhir.dosenPenguji', 'ruang', 'sesi']),
                'Data ujian TA berhasil ditambahkan'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {

        try {


            $ujianTA = UjianTA::findOrFail($id);

            $validated = $request->validate([
                'tanggal_ujian' => 'sometimes|required|date',
                'ruang_id' => 'sometimes|required|exists:ruang,id',
                'sesi_id' => 'sometimes|required|exists:sesi,id',
                'semester_id' => 'sometimes|required|exists:semester,id',
                'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajaran,id',
                'status_ujian' => 'sometimes|required|in:aktif,non-aktif'
            ]);

            // Cek apakah ada jadwal bentrok ruangan dan sesinya
            if (isset($validated['tanggal_ujian']) || isset($validated['ruang_id']) || isset($validated['sesi_id'])) {
                $cekJadwalBentrok = UjianTA::where('id', '!=', $id)
                    ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                    ->where('ruang_id', $validated['ruang_id'] ?? $ujianTA->ruang_id)
                    ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                    ->exists();
            }

            if ($cekJadwalBentrok) {
                return ApiResponse::error('Ruang dan sesi pada tanggal ini sudah terpakai', 422);
            }

            // Cek apakah ada jadwal bentrok dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($validated['tugas_akhir_id']);
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            $cekBentrokDosen = UjianTA::where('id', '!=', $id)
                ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                    $query->whereIn('dosen_id', $listDosenPenguji);
                })
                ->exists();

            if ($cekBentrokDosen) {
                return ApiResponse::error('Dosen penguji sudah ada yang ujian pada sesi ini', 422);
            }

            $ujianTA->update($validated);
            return ApiResponse::success(
                $ujianTA->fresh()->load(['tugasAkhir', 'tugasAkhir.mahasiswa', 'tugasAkhir.dosenPenguji', 'ruang', 'sesi']),
                'Data ujian TA berhasil diubah'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal diubah: ' . $e->getMessage(), 500);
        }


    }
}
