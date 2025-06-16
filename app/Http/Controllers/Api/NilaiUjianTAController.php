<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use App\Models\NilaiUjianTA;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NilaiUjianTAResource;
use Illuminate\Validation\ValidationException;


class NilaiUjianTAController extends Controller
{
    public function getNilaiUjianForMahasiswa()
    {
        $mahasiswaId = Auth::id();

        try {
            // Ambil tugas akhir mahasiswa yang aktif
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->firstOrFail();

            // Ambil ujian TA terbaru (latest)
            $ujianTA = $tugasAkhir->ujianTA()->orderByDesc('id')->first();

            if (!$ujianTA) {
                return ApiResponse::success([
                    'ujian_ta' => null,
                    'status_kelulusan_tugas_akhir' => $tugasAkhir->status_kelulusan,
                ], 'Belum ada ujian TA');
            }

            // Ambil dosen penguji
            $dosenPenguji = $tugasAkhir->dosenPenguji()->get();

            $dosenPengujiArr = $dosenPenguji->map(function ($penguji) use ($ujianTA) {
                $nilai = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                    ->where('dosen_id', $penguji->id)
                    ->first();
                return [
                    'id' => $penguji->id,
                    'nama' => $penguji->name,
                    'nilai_ujian_ta' => [
                        'id' => $nilai->id ?? null,
                        'kualitas_tata_laporan' => $nilai->kualitas_tata_laporan ?? null,
                        'kemampuan_teknis' => $nilai->kemampuan_teknis ?? null,
                        'kualitas_produk' => $nilai->kualitas_produk ?? null,
                        'presentasi' => $nilai->presentasi ?? null,
                        'rerata_ujian' => $nilai->rerata_ujian_ta ?? null,
                        'revisi' => $nilai->revisi ?? null,
                    ]
                ];
            })->toArray();

            $result = [
                'ujian_ta_id' => $ujianTA->id,
                'tanggal_ujian' => $ujianTA->tanggal_ujian,
                'status_kelulusan' => $ujianTA->status_kelulusan,
                'nilai_akhir' => $ujianTA->nilai_akhir,
                'dosen_penguji' => $dosenPengujiArr,
            ];

            return ApiResponse::success([
                'ujian_ta' => $result,
                'status_kelulusan_tugas_akhir' => $tugasAkhir->status_kelulusan,
            ], 'Data nilai ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function getNilaiUjianTAByDosen()
    {
        $dosenId = Auth::id();

        try {
            // Ambil semua tugas akhir yang memiliki dosen penguji ini
            $tugasAkhirList = TugasAkhir::whereHas('dosenPenguji', function ($query) use ($dosenId) {
                $query->where('dosen_id', $dosenId);
            })->get();

            $result = [];
            foreach ($tugasAkhirList as $tugasAkhir) {
                // Ambil semua ujian TA untuk tugas akhir ini
                $ujianTAList = $tugasAkhir->ujianTA->get();

                $ujianArr = $ujianTAList->map(function ($ujianTA) use ($dosenId) {
                    $nilai = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                        ->where('dosen_id', $dosenId)
                        ->first();
                    return [
                        'ujian_ta_id' => $ujianTA->id,
                        'tanggal_ujian' => $ujianTA->tanggal_ujian,
                        'status_kelulusan' => $ujianTA->status_kelulusan,
                        'nilai_akhir' => $ujianTA->nilai_akhir,
                        'nilai_ujian_ta' => [
                            'id' => $nilai->id ?? null,
                            'kualitas_tata_laporan' => $nilai->kualitas_tata_laporan ?? null,
                            'kemampuan_teknis' => $nilai->kemampuan_teknis ?? null,
                            'kualitas_produk' => $nilai->kualitas_produk ?? null,
                            'presentasi' => $nilai->presentasi ?? null,
                            'rerata_ujian' => $nilai->rerata_ujian_ta ?? null,
                        ]
                    ];
                });

                $result[] = [
                    'tugas_akhir_id' => $tugasAkhir->id,
                    'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir,
                    'ujian_ta' => $ujianArr,
                ];
            }

            return ApiResponse::success([
                'dosen_id' => $dosenId,
                'tugas_akhir' => $result,
            ], 'Data nilai ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    // public function storeNilaiByDosenPenguji(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
    //             'kualitas_tata_laporan' => 'required|numeric|min:0|max:100',
    //             'kemampuan_teknis' => 'required|numeric|min:0|max:100',
    //             'kualitas_produk' => 'required|numeric|min:0|max:100',
    //             'presentasi' => 'required|numeric|min:0|max:100',
    //             'rerata_ujian_ta' => 'required|numeric|min:0|max:100',
    //             'revisi' => 'nullable|string', // tambahkan ini
    //         ]);

    //         $dosenId = Auth::id();
    //         $dosenPenguji = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
    //             ->where('dosen_id', $dosenId)
    //             ->first();

    //         if (!$dosenPenguji) {
    //             return ApiResponse::error('Anda bukan dosen penguji pada tugas akhir ini.', 403);
    //         }

    //         //  Jika ini sektretaris, pastikan sudah ada 2 dosen penguji lain yang menginput nilai
    //         if ($dosenPenguji->jabatan_penguji === 'sekretaris') {
    //             $pengujiLain = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
    //                 ->where('id', '!=', $dosenPenguji->id)
    //                 ->whereNotNull('nilai_ujian_ta_id')
    //                 ->count();

    //             if ($pengujiLain < 2) {
    //                 return ApiResponse::error('Sekretaris hanya bisa submit nilai setelah 2 dosen penguji lain sudah menginput nilai.', 403);
    //             }
    //         }

    //         // Update kalau sudah ada biar tidak duplikat
    //         if ($dosenPenguji->nilai_ujian_ta_id) {
    //             return ApiResponse::error('Nilai sudah pernah diinput. Gunakan update.', 400);
    //         }

    //         // Simpan nilai ujian TA
    //         $nilaiUjianTA = $dosenPenguji->nilaiUjianTA()->create([
    //             'kualitas_tata_laporan' => $request->kualitas_tata_laporan,
    //             'kemampuan_teknis' => $request->kemampuan_teknis,
    //             'kualitas_produk' => $request->kualitas_produk,
    //             'presentasi' => $request->presentasi,
    //             'rerata_ujian_ta' => $request->rerata_ujian_ta,
    //         ]);

    //         $dosenPenguji->nilai_ujian_ta_id = $nilaiUjianTA->id;
    //         $dosenPenguji->revisi = $request->revisi;
    //         $dosenPenguji->save();

    //         return ApiResponse::success(new NilaiUjianTAResource($nilaiUjianTA), 'Nilai ujian TA berhasil disimpan');

    //     } catch (ValidationException $e) {
    //         return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
    //     }
    // }

    // public function updateNilaiByDosenPenguji(Request $request, $tugasAkhirId)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'kualitas_tata_laporan' => 'required|numeric|min:0|max:100',
    //             'kemampuan_teknis' => 'required|numeric|min:0|max:100',
    //             'kualitas_produk' => 'required|numeric|min:0|max:100',
    //             'presentasi' => 'required|numeric|min:0|max:100',
    //             'rerata_ujian_ta' => 'required|numeric|min:0|max:100',
    //             'revisi' => 'nullable|string', // tambahkan ini
    //         ]);

    //         $dosenId = Auth::id();
    //         $dosenPenguji = DosenPenguji::where('tugas_akhir_id', $tugasAkhirId)
    //             ->where('dosen_id', $dosenId)
    //             ->first();

    //         if (!$dosenPenguji) {
    //             return ApiResponse::error('Anda bukan dosen penguji pada tugas akhir ini.', 403);
    //         }

    //         $nilaiUjianTA = $dosenPenguji->nilaiUjianTA;
    //         if (!$nilaiUjianTA) {
    //             return ApiResponse::error('Nilai belum ada, gunakan create.', 404);
    //         }

    //         // Update nilai ujian TA
    //         $nilaiUjianTA->update([
    //             'kualitas_tata_laporan' => $validatedData['kualitas_tata_laporan'],
    //             'kemampuan_teknis' => $validatedData['kemampuan_teknis'],
    //             'kualitas_produk' => $validatedData['kualitas_produk'],
    //             'presentasi' => $validatedData['presentasi'],
    //             'rerata_ujian_ta' => $validatedData['rerata_ujian_ta'],
    //         ]);

    //         // Update revisi di pivot dosen_penguji
    //         $dosenPenguji->revisi = $request->revisi;
    //         $dosenPenguji->save();

    //         return ApiResponse::success(new NilaiUjianTAResource($nilaiUjianTA), 'Nilai ujian TA & revisi berhasil diperbarui');

    //     } catch (ValidationException $e) {
    //         return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
    //     }
    // }
}
