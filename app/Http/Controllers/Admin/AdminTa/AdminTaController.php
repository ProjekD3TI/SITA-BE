<?php

namespace App\Http\Controllers\Admin\AdminTa;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\TugasAkhir;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToArray;

class AdminTaController extends Controller
{
    public function dashboard()
    {
        try {

            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'ujianTA', 'proposal'])
                ->where('status_aktif', 1)
                ->get();

            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Tidak ada tugas akhir yang aktif');
            }

            $proposal = $tugasAkhir->pluck('proposal')->filter();
            $tanggal_ujian = $tugasAkhir->pluck('ujianTA')->flatten(1);
            $tableValidasi = $tugasAkhir
                ->filter(function ($ta) {
                    return $ta->proposal && $ta->proposal->status_proposal == 0;
                })
                ->map(function ($ta) {
                    return [
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'mahasiswa' => $ta->mahasiswa->name ?? null,
                        'judul' => $ta->proposal->judul_proposal ?? null,
                        'status' => $ta->proposal->status_proposal ?? null,
                    ];
                })
                ->values();

            $tabelUjian = $tugasAkhir
                ->where('status_valid', '0')
                ->map(function ($ta) {
                    return [
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'mahasiswa' => $ta->mahasiswa->name ?? null,
                        'judul' => $ta->judul_tugas_akhir ?? null,
                        'status' => $ta->status_valid ?? null,
                    ];
                })
                ->values();
            $data = [
                'validasi_berkas' => [
                    'prosposal' => [
                        'total' => $proposal->count() ?? null,
                        'valid' => $proposal->where('status_proposal', 1)->count() ?? null,
                        'menunggu' => $proposal->where('status_proposal', 0)->count() ?? null,
                    ],
                    'ujian_tugas_akhir' => [
                        'total' => $tugasAkhir->count() ?? null,
                        'valid' => $tugasAkhir->where('status_valid', 1)->count() ?? null,
                        'menunggu' => $tugasAkhir->where('status_valid', '0')->count() ?? null,
                    ],
                ],
                'summary_card' => [
                    'tugas_akhir_aktif' => $tugasAkhir->count() ?? null,
                    'ujian_terjadwal' => $tanggal_ujian->count() ?? null,
                    'mahasiswa_tugas_akhir' => $tugasAkhir->count() ?? null,
                ],
                'tabel_validasi_berkas_sempro' => $tableValidasi ?? null,
                'tabel_validasi_berkas_ujian' => $tabelUjian ?? null,

            ];
            return ApiResponse::success($data, 'Data berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
