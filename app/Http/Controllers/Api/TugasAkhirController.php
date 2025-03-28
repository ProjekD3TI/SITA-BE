<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;
use Illuminate\Validation\ValidationException;


class TugasAkhirController extends Controller
{
    public function index(){
        try {
            $requests = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->get();
            return ApiResponse::success(TugasAkhirResource::collection($requests));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getRegisterTA()
    {
        try {
            // Ambil data TugasAkhir dengan status_valid = 0 menggunakan eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])
                ->where('status_valid', 0)
                ->get();

            // Kembalikan data menggunakan resource
            return ApiResponse::success(TugasAkhirResource::collection($tugasAkhir), 'Tugas Akhir dengan status valid = 0 berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateActiveStatus(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status_aktif' => 'required|in:0,1,2',
            ]);

            // Find the record by ID with eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Update the status_aktif
            $tugasAkhir->status_aktif = $validatedData['status_aktif'];
            $tugasAkhir->save();

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Pendaftaran Ujian TA sukses');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateTugasAkhirByMahasiswa(Request $request, $id)
    {
        try {
            // Validasi semua atribut yang tidak boleh null
            $validatedData = $request->validate([
                'judul_tugas_akhir' => 'required|string|max:255',
                'krs' => 'required|file|mimes:pdf',
                'persetujuan_pembimbing' => 'required|file|mimes:pdf',
                'sertifikat_kompetensi' => 'required|file|mimes:pdf',
                'toeic' => 'required|file|mimes:pdf',
                'berita_acara_semhas' => 'required|file|mimes:pdf',
                'naskah_ta' => 'required|file|mimes:pdf',
                'masa_perpanjangan_studi' => 'nullable|date',
                // 'nilai_proposal' => 'required|numeric|min:0|max:100',
                // 'nilai_bimbingan' => 'required|numeric|min:0|max:100',
                // 'catatan_proposal' => 'required|string',
                'file_transkrip' => 'required|file|mimes:pdf',
            ]);

            // Cari TugasAkhir milik mahasiswa dengan eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])
                ->where('id', $id)
                ->where('mahasiswa_id', $request->user()->id) // Pastikan mahasiswa memiliki record ini
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan atau unauthorized', 404);
            }

             // Tambahkan kondisi untuk status_valid
            if (!is_null($tugasAkhir->status_valid) && !in_array($tugasAkhir->status_valid, [2])) {
                return ApiResponse::error('Tugas Akhir tidak dapat diperbarui karena status validasi tidak sesuai', 403);
            }

            // Perbarui data TugasAkhir
            $tugasAkhir->judul_tugas_akhir = $validatedData['judul_tugas_akhir'];
            $tugasAkhir->krs = $request->file('krs')->store('krs');
            $tugasAkhir->persetujuan_pembimbing = $request->file('persetujuan_pembimbing')->store('persetujuan_pembimbing');
            $tugasAkhir->sertifikat_kompetensi = $request->file('sertifikat_kompetensi')->store('sertifikat_kompetensi');
            $tugasAkhir->toeic = $request->file('toeic')->store('toeic');
            $tugasAkhir->berita_acara_semhas = $request->file('berita_acara_semhas')->store('berita_acara_semhas');
            $tugasAkhir->naskah_ta = $request->file('naskah_ta')->store('naskah_ta');
            $tugasAkhir->masa_perpanjangan_studi = $validatedData['masa_perpanjangan_studi'];
            $tugasAkhir->file_transkrip = $request->file('file_transkrip')->store('file_transkrip');
            $tugasAkhir->status_valid = 0; // Set status_valid menjadi 0 (menunggu validasi)
            $tugasAkhir->save();

            // Kembalikan data TugasAkhir yang diperbarui menggunakan resource
            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Tugas Akhir berhasil diperbarui dan menunggu validasi');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateValidationStatusByAdmin(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status_valid' => 'required|in:1,2', // 1 = valid, 2 = invalid
            ]);

            // Find the TugasAkhir record
            $tugasAkhir = TugasAkhir::find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Update the validation status
            $tugasAkhir->status_valid = $validatedData['status_valid'];
            $tugasAkhir->save();

            $statusMessage = $validatedData['status_valid'] == 1 ? 'valid' : 'invalid';
            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), "Tugas Akhir status diubah menjadi $statusMessage");
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }
}
