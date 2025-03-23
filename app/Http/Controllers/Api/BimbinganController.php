<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BimbinganResource;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Bimbingan;
use App\Models\TugasAkhir;
use Illuminate\Validation\ValidationException;
class BimbinganController extends Controller
{

    public function getBimbinganByDosen()
    {
        $dosenId = Auth::id();

        try {
            // Retrieve TugasAkhir records where dosen_pembimbing_id matches the authenticated dosen's ID
            $tugasAkhirIds = TugasAkhir::where('dosen_pembimbing_id', $dosenId)->pluck('id');

            // Retrieve Bimbingan records where tugas_akhir_id is in the retrieved TugasAkhir IDs
            $bimbingan = Bimbingan::with(['tugasAkhir', 'dosenPembimbing'])
                ->whereIn('tugas_akhir_id', $tugasAkhirIds)
                ->get();

            // $bimbingan = Bimbingan::with(['tugasAkhir', 'dosenPembimbing'])
            //     ->where('dosen_pembimbing_id', $dosenId)
            //     ->get();

            $data = BimbinganResource::collection($bimbingan);

            return ApiResponse::success($data, 'Data bimbingan berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data bimbingan gagal diambil ' . $e->getMessage(), 500);
        }
    }

    public function updateVerifikasiBimbinganByDosen(Request $request, $id)
{
    $dosenId = Auth::id();

    try {
        // Find the Bimbingan record where the dosen_pembimbing_id matches the authenticated dosen's ID and the Bimbingan ID matches the provided ID
        $bimbingan = Bimbingan::whereHas('tugasAkhir', function ($query) use ($dosenId) {
            $query->where('dosen_pembimbing_id', $dosenId);
        })->where('id', $id)->firstOrFail();

        // Validate the request
        $validated = $request->validate([
            'status_verifikasi' => 'required|integer',
        ]);

        // Update the Bimbingan record with the validated data
        $bimbingan->update($validated);

        // Return a success response with the updated Bimbingan resource
        return ApiResponse::success(
            new BimbinganResource($bimbingan->load(['tugasAkhir', 'dosenPembimbing'])),
            'Status verifikasi berhasil diubah'
        );
    } catch (ValidationException $e) {
        // Return a validation error response
        return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
    } catch (\Exception $e) {
        // Return a general error response
        return ApiResponse::error('Status verifikasi gagal diubah ' . $e->getMessage(), 500);
    }
}

    // public function updateVerifikasiBimbinganByDosen(Request $request, $id)
    // {
    //     $dosenId = Auth::id();

    //     try {
    //         $bimbingan = Bimbingan::where('dosen_pembimbing_id', $dosenId)
    //             ->where('id', $id)
    //             ->firstOrFail();

    //         $validated = $request->validate([
    //             'status_verifikasi' => 'required|integer',
    //         ]);

    //         $bimbingan->update($validated);
    //         return ApiResponse::success(
    //             new BimbinganResource($bimbingan->load(['tugasAkhir', 'dosenPembimbing'])),
    //             'Status verifikasi berhasil diubah'
    //         );
    //     } catch (ValidationException $e) {
    //         return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error('Status verifikasi gagal diubah ' . $e->getMessage(), 500);
    //     }
    // }

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

            $bimbingan = Bimbingan::with(['tugasAkhir', 'dosenPembimbing'])
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
                // 'tanggal_bimbingan' => 'required|date'
            ]);

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan', 404);
            }

            // Check if there is any previous Bimbingan with status_verifikasi = 0
            $previousBimbingan = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
            ->where('status_verifikasi', 0)
            ->first();

            if ($previousBimbingan) {
                return ApiResponse::error('Tidak dapat membuat bimbingan baru karena bimbingan sebelumnya belum diverifikasi', 400);
            }

            $bimbingan = Bimbingan::create([
                // 'dosen_pembimbing_id' => $tugasAkhir->dosen_pembimbing_id,
                'tugas_akhir_id' => $tugasAkhir->id,
                'catatan' => $validated['catatan'],
                // 'tanggal_bimbingan' => $validated['tanggal_bimbingan']
            ]);

            return ApiResponse::success(
                new BimbinganResource($bimbingan->load(['tugasAkhir', 'dosenPembimbing'])),
                'Bimbingan berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Bimbingan gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }
}