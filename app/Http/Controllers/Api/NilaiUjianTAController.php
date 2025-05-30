<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NilaiUjianTAResource;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


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

    public function storeNilaiByDosenPenguji(Request $request)
    {
        try {
            $request->validate([
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
                'kualitas_tata_laporan' => 'required|numeric|min:0|max:100',
                'kemampuan_teknis' => 'required|numeric|min:0|max:100',
                'kualitas_produk' => 'required|numeric|min:0|max:100',
                'presentasi' => 'required|numeric|min:0|max:100',
                'rerata_ujian_ta' => 'required|numeric|min:0|max:100',
                'revisi' => 'nullable|string', // tambahkan ini
            ]);

            $dosenId = Auth::id();
            $dosenPenguji = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
                ->where('dosen_id', $dosenId)
                ->first();

            if (!$dosenPenguji) {
                return ApiResponse::error('Anda bukan dosen penguji pada tugas akhir ini.', 403);
            }

            // Update kalau sudah ada biar tidak duplikat
            if ($dosenPenguji->nilai_ujian_ta_id) {
                return ApiResponse::error('Nilai sudah pernah diinput. Gunakan update.', 400);
            }

            // Simpan nilai ujian TA
            $nilaiUjianTA = $dosenPenguji->nilaiUjianTA()->create([
                'kualitas_tata_laporan' => $request->kualitas_tata_laporan,
                'kemampuan_teknis' => $request->kemampuan_teknis,
                'kualitas_produk' => $request->kualitas_produk,
                'presentasi' => $request->presentasi,
                'rerata_ujian_ta' => $request->rerata_ujian_ta,
            ]);

            $dosenPenguji->nilai_ujian_ta_id = $nilaiUjianTA->id;
            $dosenPenguji->revisi = $request->revisi;
            $dosenPenguji->save();

            return ApiResponse::success(new NilaiUjianTAResource($nilaiUjianTA), 'Nilai ujian TA berhasil disimpan');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateNilaiByDosenPenguji(Request $request, $tugasAkhirId)
    {
        try {
            $validatedData = $request->validate([
                'kualitas_tata_laporan' => 'required|numeric|min:0|max:100',
                'kemampuan_teknis' => 'required|numeric|min:0|max:100',
                'kualitas_produk' => 'required|numeric|min:0|max:100',
                'presentasi' => 'required|numeric|min:0|max:100',
                'rerata_ujian_ta' => 'required|numeric|min:0|max:100',
                'revisi' => 'nullable|string', // tambahkan ini
            ]);

            $dosenId = Auth::id();
            $dosenPenguji = DosenPenguji::where('tugas_akhir_id', $tugasAkhirId)
                ->where('dosen_id', $dosenId)
                ->first();

            if (!$dosenPenguji) {
                return ApiResponse::error('Anda bukan dosen penguji pada tugas akhir ini.', 403);
            }

            $nilaiUjianTA = $dosenPenguji->nilaiUjianTA;
            if (!$nilaiUjianTA) {
                return ApiResponse::error('Nilai belum ada, gunakan create.', 404);
            }

            // Update nilai ujian TA
            $nilaiUjianTA->update([
                'kualitas_tata_laporan' => $validatedData['kualitas_tata_laporan'],
                'kemampuan_teknis' => $validatedData['kemampuan_teknis'],
                'kualitas_produk' => $validatedData['kualitas_produk'],
                'presentasi' => $validatedData['presentasi'],
                'rerata_ujian_ta' => $validatedData['rerata_ujian_ta'],
            ]);

            // Update revisi di pivot dosen_penguji
            $dosenPenguji->revisi = $request->revisi;
            $dosenPenguji->save();

            return ApiResponse::success(new NilaiUjianTAResource($nilaiUjianTA), 'Nilai ujian TA & revisi berhasil diperbarui');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }
}
