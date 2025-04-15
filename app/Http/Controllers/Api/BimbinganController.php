<?php

namespace App\Http\Controllers\Api;

use App\Models\Bimbingan;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\BimbinganResource;
use App\Http\Resources\TugasAkhirResource;

class BimbinganController extends Controller
{

    public function getTugasAkhirByDosen()
    {
        $dosenId = Auth::id();

        try {
            // Mencari kumpulan Id TugasAkhir yang memiliki dosen_pembimbing_id yang sama dengan dosenId (dosen yang sedang login)
            $daftarTugasAkhir = TugasAkhir::where('dosen_pembimbing_id', $dosenId)->get();

            return ApiResponse::success(TugasAkhirResource::collection($daftarTugasAkhir), 'Data tugas akhir berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data tugas akhir gagal diambil ' . $e->getMessage(), 500);
        }
    }


    public function getBimbinganByTugasAkhir($tugasAkhirId)
    {
        $dosenId = Auth::id();
        try {
            // Cek apakah tugas akhir yang dipilih dosen sesuai dengan dosen yang sedang login
            $tugasAkhir = TugasAkhir::where('id', $tugasAkhirId)->where('dosen_pembimbing_id', $dosenId)->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan atau tidak sesuai dengan dosen yang sedang login', 404);
            }

            // Mengambil data bimbingan berdasarkan tugas akhir yang dipilih dosen
            $bimbingan = Bimbingan::with(['tugasAkhir']) // Load tugasAkhir relationship
                ->where('tugas_akhir_id', $tugasAkhirId)
                ->get();

            return ApiResponse::success(BimbinganResource::collection($bimbingan), 'Data bimbingan berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data bimbingan gagal diambil ' . $e->getMessage(), 500);
        }
    }

    public function updateVerifikasiBimbinganByDosen(Request $request, $id)
    {
        $dosenId = Auth::id();

        try {

            $validated = $request->validate([
                'status_verifikasi' => 'required|integer|in:0,1,2',
            ]);

            // Mencari data bimbingan berdasarkan id yang dipilih dosen
            $bimbingan = Bimbingan::whereHas(
                'tugasAkhir',
                function ($query) use ($dosenId) {
                    $query->where('dosen_pembimbing_id', $dosenId);
                }
            )->where('id', $id)->firstOrFail();


            $bimbingan->update($validated);

            $bimbingan->load(['tugasAkhir.dosenPembimbing']);

            return ApiResponse::success(
                new BimbinganResource($bimbingan),
                'Status verifikasi berhasil diubah'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Status verifikasi gagal diubah ' . $e->getMessage(), 500);
        }
    }

    public function getBimbinganByMahasiswa()
    {
        $mahasiswa = Auth::id();
        try {

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan', 404);
            }

            $bimbingan = Bimbingan::with(['tugasAkhir'])
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->get();

            $data = BimbinganResource::collection($bimbingan);

            return ApiResponse::success($data, 'Data bimbingan berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data bimbingan gagal diambil ' . $e->getMessage(), 500);
        }
    }

    public function storeBimbinganByMahasiswa(Request $request)
    {
        try {
            $mahasiswa = Auth::id();
            $validated = $request->validate([
                'catatan' => 'required|string',
                'tanggal_bimbingan' => 'required|date'
            ]);

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->firstOrFail();

            // Cek apakah ada bimbingan sebelumnya yang belum diverifikasi
            $previousBimbingan = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
                ->where('status_verifikasi', 0)
                ->exists();

            if ($previousBimbingan) {
                return ApiResponse::error('Tidak dapat membuat bimbingan baru karena bimbingan sebelumnya belum diverifikasi', 400);
            }

            $bimbingan = Bimbingan::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'catatan' => $validated['catatan'],
                'tanggal_bimbingan' => $validated['tanggal_bimbingan']
            ]);

            return ApiResponse::success(
                new BimbinganResource($bimbingan->load(['tugasAkhir'])),
                'Bimbingan berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Bimbingan gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }

    public function updateBimbinganByMahasiswa(Request $request, $id)
    {
        try {
            $mahasiswa = Auth::id();
            $validated = $request->validate([
                'catatan' => 'required|string',
                'tanggal_bimbingan' => 'required|date'
            ]);

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->firstOrFail();

            $bimbingan = Bimbingan::where('id', $id)
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->firstOrFail();


            // Cek apakah bimbingan sudah diverifikasi
            if ($bimbingan->status_verifikasi != 0) {
                return ApiResponse::error('Bimbingan sudah diverifikasi dan tidak dapat diubah', 400);
            }

            $bimbingan->update($validated);

            return ApiResponse::success(
                new BimbinganResource($bimbingan->load(['tugasAkhir'])),
                'Data bimbingan berhasil diubah'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Bimbingan gagal diubah: ' . $e->getMessage(), 500);
        }
    }

    public function destroyBimbinganByMahasiswa($id)
    {
        try {
            $mahasiswa = Auth::id();

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->firstOrFail();

            $bimbingan = Bimbingan::where('id', $id)
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->firstOrFail();

            // Cek apakah bimbingan sudah diverifikasi
            if ($bimbingan->status_verifikasi != 0) {
                return ApiResponse::error('Bimbingan sudah diverifikasi dan tidak dapat dihapus', 400);
            }

            $bimbingan->delete();
            return ApiResponse::success(null, 'Data bimbingan berhasil dihapus');
        } catch (\Exception $e) {
            return ApiResponse::error('Bimbingan gagal dihapus: ' . $e->getMessage(), 500);
        }
    }

}
