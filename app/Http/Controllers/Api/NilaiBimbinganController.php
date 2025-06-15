<?php

namespace App\Http\Controllers\Api;

use App\Models\TugasAkhir;
use App\Models\NilaiUjianTA;
use Illuminate\Http\Request;
use App\Models\NilaiBimbingan;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\NilaiBimbinganResource;
use Illuminate\Validation\ValidationException;

class NilaiBimbinganController extends Controller
{

    // ========================== FITUR DOSEN ==========================

    public function getNilaiBimbinganByDosen($tugasAkhirId)
    {
        $dosenId = Auth::id();

        try {
            $tugasAkhir = TugasAkhir::where('id', $tugasAkhirId)
                ->where('dosen_pembimbing_id', $dosenId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan atau tidak sesuai dengan dosen yang sedang login', 404);
            }

            $nilaiBimbingan = $tugasAkhir->nilaiBimbingan;
            if (!$nilaiBimbingan) {
                return ApiResponse::error('Nilai bimbingan tidak ditemukan untuk tugas akhir ini', 404);
            }

            return ApiResponse::success(new NilaiBimbinganResource($nilaiBimbingan), 'Data nilai bimbingan berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai bimbingan gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function storeNilaiBimbinganByDosen(Request $request, $tugasAkhirId)
    {
        $dosenId = Auth::id();

        try {
            $validatedData = $request->validate([
                'rerata_bimbingan' => 'required|numeric|min:0|max:100',
                'disiplin' => 'required|numeric|min:0|max:100',
                'komunikasi' => 'required|numeric|min:0|max:100',
                'skill_pengetahuan' => 'required|numeric|min:0|max:100',
                'tingkah_laku' => 'required|numeric|min:0|max:100',
                'catatan_akhir_bimbingan' => 'nullable|string',
            ]);

            // Cari tugas akhir berdasarkan id dan dosen pembimbing
            $tugasAkhir = TugasAkhir::where('id', $tugasAkhirId)
                ->where('dosen_pembimbing_id', $dosenId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan atau tidak sesuai dengan dosen yang sedang login', 404);
            }

            // Periksa apakah nilai bimbingan sudah ada
            $nilaiBimbingan = $tugasAkhir->nilaiBimbingan;
            if ($nilaiBimbingan) {
                return ApiResponse::error('Nilai bimbingan sudah ada untuk tugas akhir ini', 400);
            }

            // Buat nilai bimbingan baru
            $nilaiBimbingan = NilaiBimbingan::create($validatedData);

            // Hubungkan nilai bimbingan dengan tugas akhir
            $tugasAkhir->update([
                'nilai_bimbingan_id' => $nilaiBimbingan->id,
            ]);

            // ================== Tambahan: Cek 3 dosen penguji sudah memberi nilai ==================
            $dosenPenguji = $tugasAkhir->dosenPenguji()->withPivot('nilai_ujian_ta_id')->get();

            $nilaiUjianTA = collect();
            foreach ($dosenPenguji as $penguji) {
                if ($penguji->pivot->nilai_ujian_ta_id) {
                    $nilaiUjianTAmodel = NilaiUjianTA::find($penguji->pivot->nilai_ujian_ta_id);
                    if ($nilaiUjianTAmodel && $nilaiUjianTAmodel->rerata_ujian_ta !== null) {
                        $nilaiUjianTA->push($nilaiUjianTAmodel->rerata_ujian_ta);
                    }
                }
            }
            $nilaiUjianTA = $nilaiUjianTA->take(3);
            $nilaiProposal = $tugasAkhir->nilai_proposal;


            if ($nilaiProposal !== null && $nilaiBimbingan->rerata_bimbingan !== null && $nilaiUjianTA->count() === 3) {
                $rerataUjianTA = $nilaiUjianTA->avg();
                // Perhitungan dengan bobot
                $nilaiAkhir = round(
                    ($nilaiProposal * 0.1) +
                    ($nilaiBimbingan->rerata_bimbingan * 0.3) +
                    ($rerataUjianTA * 0.6),
                    2
                );
                $tugasAkhir->update([
                    'nilai_akhir' => $nilaiAkhir,
                ]);
            }
            // ===================================================================

            return ApiResponse::success(new NilaiBimbinganResource($nilaiBimbingan), 'Data nilai bimbingan berhasil disimpan');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai bimbingan gagal disimpan: ' . $e->getMessage(), 500);
        }
    }

    public function updateNilaiBimbinganByDosen(Request $request, $tugasAkhirId)
    {
        $dosenId = Auth::id();

        try {
            $validatedData = $request->validate([
                'rerata_bimbingan' => 'required|numeric|min:0|max:100',
                'disiplin' => 'required|numeric|min:0|max:100',
                'komunikasi' => 'required|numeric|min:0|max:100',
                'skill_pengetahuan' => 'required|numeric|min:0|max:100',
                'tingkah_laku' => 'required|numeric|min:0|max:100',
                'catatan_akhir_bimbingan' => 'nullable|string',
            ]);

            // Cari tugas akhir berdasarkan id dan dosen pembimbing
            $tugasAkhir = TugasAkhir::where('id', $tugasAkhirId)
                ->where('dosen_pembimbing_id', $dosenId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan atau tidak sesuai dengan dosen yang sedang login', 404);
            }

            // Periksa apakah nilai bimbingan sudah ada
            $nilaiBimbingan = $tugasAkhir->nilaiBimbingan;
            if (!$nilaiBimbingan) {
                return ApiResponse::error('Nilai bimbingan tidak ditemukan untuk tugas akhir ini', 404);
            }

            // Perbarui nilai bimbingan
            $nilaiBimbingan->update($validatedData);

            return ApiResponse::success(new NilaiBimbinganResource($nilaiBimbingan), 'Data nilai bimbingan berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai bimbingan gagal diperbarui: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DOSEN ==========================



    // ========================== FITUR MAHASISWA ==========================

    public function getNilaiBimbinganByMahasiswa()
    {
        $mahasiswaId = Auth::id();

        try {
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)->where('status_aktif', 1)->firstOrFail();

            $nilaiBimbingan = $tugasAkhir->nilaiBimbingan;
            if (!$nilaiBimbingan) {
                return ApiResponse::error('Nilai bimbingan tidak ditemukan untuk tugas akhir ini', 404);
            }

            return ApiResponse::success(new NilaiBimbinganResource($nilaiBimbingan), 'Data nilai bimbingan berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai bimbingan gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR MAHASISWA ==========================

}