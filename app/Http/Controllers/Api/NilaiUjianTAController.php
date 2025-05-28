<?php

namespace App\Http\Controllers\Api;

use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class NilaiUjianTAController extends Controller
{
    public function getNilaiUjianForMhs()
    {
        $mahasiswaId = Auth::id();

        try {
            // Ambil tugas akhir mahasiswa yang aktif
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->firstOrFail();

            // Ambil dosen penguji dari tabel pivot, beserta relasi dosen dan nilaiUjianTA
            $dosenPenguji = DosenPenguji::where('tugas_akhir_id', $tugasAkhir->id)
                ->with(['dosen', 'nilaiUjianTA'])
                ->get();

            if ($dosenPenguji->isEmpty()) {
                return ApiResponse::error("Tidak ada dosen penguji untuk tugas akhir dengan ID {$tugasAkhir->id}", 404);
            }

            $dosenPengujiArr = $dosenPenguji->map(function ($penguji) {
                $nilai = $penguji->nilaiUjianTA;
                return [
                    'id' => $penguji->dosen->id ?? null,
                    'nama' => $penguji->dosen->name ?? null,
                    'revisi' => $penguji->revisi ?? null,
                    'nilai_ujian_ta' => [
                        'id' => $nilai->id ?? null,
                        'kualitas_tata_laporan' => $nilai->kualitas_tata_laporan ?? null,
                        'kemampuan_teknis' => $nilai->kemampuan_teknis ?? null,
                        'kualitas_produk' => $nilai->kualitas_produk ?? null,
                        'presentasi' => $nilai->presentasi ?? null,
                        'rerata_ujian' => $nilai->rerata_ujian_ta ?? null,
                    ]
                ];
            })->toArray();

            return ApiResponse::success([
                'dosen_penguji' => $dosenPengujiArr
            ], 'Data nilai ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function getNilaiUjianTAByDosen()
    {
        $dosenId = Auth::id();

        try {
            // Ambil tugas akhir yang memiliki dosen penguji sesuai dengan ID dosen
            $tugasAkhir = TugasAkhir::whereHas('dosenPenguji', function ($query) use ($dosenId) {
                $query->where('dosen_id', $dosenId);
            })->with('nilaiUjianTA')->get();

            // Jika tidak ada tugas akhir, kembalikan error
            if ($tugasAkhir->isEmpty()) {
                return ApiResponse::error("Tidak ada tugas akhir yang terkait dengan dosen penguji dengan ID {$dosenId}", 404);
            }

            // Kembalikan data nilai ujian TA
            return ApiResponse::success([
                'dosen_id' => $dosenId,
                'tugas_akhir' => $tugasAkhir,
            ], 'Data nilai ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }
}
