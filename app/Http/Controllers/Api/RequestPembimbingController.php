<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\RequestPembimbing;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RequestPembimbingResource;
use Illuminate\Validation\ValidationException;


class RequestPembimbingController extends Controller
{

    public function storeWithTugasAkhir(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dosen_id' => 'required|integer',
                'kategori_id' => 'required|integer',
            ]);

            $mahasiswa_id = Auth::id();

            // Cek apakah mahasiswa sudah memiliki TugasAkhir aktif
            $existingTugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa_id)
                ->where('status_aktif', 1)
                ->first();

            if ($existingTugasAkhir) {
                return ApiResponse::error('Mahasiswa sudah memiliki tugas akhir yang aktif', 400);
            }

            // Create TugasAkhir
            $tugasAkhir = TugasAkhir::create([
                'mahasiswa_id' => $mahasiswa_id,
                'kategori_id' => $validatedData['kategori_id'],
                'status_aktif' => 1,
            ]);

            // Buat RequestPembimbing
            $requestPembimbing = RequestPembimbing::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'dosen_id' => $validatedData['dosen_id'],
                'status_request' => 0,
                'tanggal_respon' => null,
            ]);

            return ApiResponse::success(new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')), 'Request pembimbing dan Tugas Akhir berhasil dibuat', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Data request pembimbing gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }

    public function updateApprovalForDosen(Request $request, $id)
    {
        try {
            $requestPembimbing = RequestPembimbing::with(['tugasAkhir', 'dosen'])->find($id);

            if (!$requestPembimbing) {
                return ApiResponse::error('Request not found', 404);
            }

            // Check if the logged-in user is the dosen assigned to this request
            $dosenId = Auth::user()->id;
            if ($requestPembimbing->dosen_id != $dosenId) {
                return ApiResponse::error('Unauthorized', 403);
            }

            $validatedData = $request->validate([
                'status_request' => 'required|integer',
                'tanggal_respon' => 'sometimes|integer',
            ]);

            $requestPembimbing->update([
                'status_request' => $validatedData['status_request'],
                'tanggal_respon' => Carbon::now(),
            ]);

            // Update dosen_pembimbing_id in TugasAkhir if status_request is 1
            if ($validatedData['status_request'] == 1) {
                $tugasAkhir = TugasAkhir::find($requestPembimbing->tugas_akhir_id);
                if ($tugasAkhir) {
                    $tugasAkhir->dosen_pembimbing_id = $requestPembimbing->dosen_id;
                    $tugasAkhir->save();
                }
            }

            return ApiResponse::success(new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')), 'Request updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // Fungsi untuk mahasiswa
    public function cekStatusRequestByMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();

            // Ambil data RequestPembimbing berdasarkan mahasiswa_id melalui relasi TugasAkhir
            $requestPembimbing = RequestPembimbing::whereHas('tugasAkhir', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            })->first();

            // Jika tidak ditemukan, kembalikan null untuk status_request
            if (!$requestPembimbing) {
                return ApiResponse::success([
                    'status_request' => null,
                ], 'Belum ada request pembimbing');
            }

            return ApiResponse::success([
                'status_request' => $requestPembimbing->status_request,
            ], 'Request sudah direspon');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

}
