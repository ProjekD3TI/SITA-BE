<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NilaiBimbinganResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ApiResponse;
use App\Models\NilaiBimbingan;
use App\Models\TugasAkhir;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;

class NilaiBimbinganController extends Controller
{
    public function getNilaiBimbinganByMahasiswa()
    {
        $mahasiswaId = Auth::id();

        try {
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)->first();
            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan untuk mahasiswa ini', 404);
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

    public function getNilaiBimbinganByDosen($tugasAkhirId)
    {
        $dosenId = Auth::id();

        try {
            // Mencari tugas akhir berdasarkan id dan dosen pembimbing
            $tugasAkhir = TugasAkhir::where('id', $tugasAkhirId)
                ->where('dosen_pembimbing_id', $dosenId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan atau tidak sesuai dengan dosen yang sedang login', 404);
            }

            // Method untuk mengambil nilai bimbingan
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
                'nilai_akhir_bimbingan' => 'required|numeric|min:0|max:100',
                'nilai_disiplin' => 'required|numeric|min:0|max:100',
                'nilai_komunikasi' => 'required|numeric|min:0|max:100',
                'nilai_skill_pengetahuan' => 'required|numeric|min:0|max:100',
                'nilai_tingkah_laku' => 'required|numeric|min:0|max:100',
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

            return ApiResponse::success(new NilaiBimbinganResource($nilaiBimbingan), 'Data nilai bimbingan berhasil disimpan');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data nilai bimbingan gagal disimpan: ' . $e->getMessage(), 500);
        }
    }

}
