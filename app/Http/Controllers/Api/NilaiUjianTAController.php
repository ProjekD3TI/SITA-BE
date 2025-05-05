<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\TugasAkhir;


class NilaiUjianTAController extends Controller
{
    public function getNilaiUjianTAByMahasiswa()
    {
        $mahasiswaId = Auth::id();

        try {
            // Ambil tugas akhir mahasiswa yang aktif
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->firstOrFail();

            // Ambil dosen penguji yang terkait dengan tugas akhir
            $dosenPenguji = $tugasAkhir->dosenPenguji()->with(['dosen', 'nilaiUjianTA'])->get();

            // Jika tidak ada dosen penguji, kembalikan error
            if ($dosenPenguji->isEmpty()) {
                return ApiResponse::error("Tidak ada dosen penguji untuk tugas akhir dengan ID {$tugasAkhir->id}", 404);
            }

            // Ambil nilai ujian TA dari setiap dosen penguji
            $nilaiUjianTA = $dosenPenguji->map(function ($penguji) {
                return [
                    'dosen_penguji' => $penguji->dosen->nama,
                    'nilai_ujian_ta' => optional($penguji->nilaiUjianTA)->only([
                        'kualitas_tata_laporan',
                        'kemampuan_teknis',
                        'kualitas_produk',
                        'presentasi',
                        'rerata_ujian_ta'
                    ]),
                ];
            });

            // Kembalikan data nilai ujian TA
            return ApiResponse::success([
                'mahasiswa_id' => $mahasiswaId,
                'nama_mahasiswa' => $tugasAkhir->mahasiswa->nama, // Pastikan relasi mahasiswa dimuat
                'tugas_akhir_id' => $tugasAkhir->id,
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir,
                'nilai_ujian' => $nilaiUjianTA,
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
