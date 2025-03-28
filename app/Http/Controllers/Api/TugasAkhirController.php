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

    public function updateActiveStatus(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status_aktif' => 'required|in:0,1,2',
            ]);

            // Find the record by ID with eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Record not found', 404);
            }

            // Update the status_aktif
            $tugasAkhir->status_aktif = $validatedData['status_aktif'];
            $tugasAkhir->save();

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Status updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // public function updateTugasAkhirByMahasiswa(Request $request, $id)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'judul_tugas_akhir' => 'required|string|max:255'
    //         ]);

    //         // Find the TugasAkhir record with eager loading
    //         $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])
    //             ->where('id', $id)
    //             ->where('mahasiswa_id', $request->user()->id) // Ensure the student owns the record
    //             ->first();

    //         if (!$tugasAkhir) {
    //             return ApiResponse::error('Tugas Akhir not found or unauthorized', 404);
    //         }

    //         // Update the TugasAkhir data
    //         $tugasAkhir->judul = $validatedData['judul'];
    //         $tugasAkhir->deskripsi = $validatedData['deskripsi'] ?? $tugasAkhir->deskripsi;
    //         $tugasAkhir->status_valid = 0; // Set status_valid to 0 (pending validation)
    //         $tugasAkhir->save();

    //         // Return the updated TugasAkhir using a resource
    //         return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Tugas Akhir updated successfully and is pending validation');
    //     } catch (ValidationException $e) {
    //         return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
    //     } catch (Exception $e) {
    //         return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
    //     }
    // }

    // public function updateValidationStatusByAdmin(Request $request, $id)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'status_valid' => 'required|in:1,2', // 1 = valid, 2 = invalid
    //         ]);

    //         // Find the TugasAkhir record
    //         $tugasAkhir = TugasAkhir::find($id);

    //         if (!$tugasAkhir) {
    //             return ApiResponse::error('Tugas Akhir not found', 404);
    //         }

    //         // Update the validation status
    //         $tugasAkhir->status_valid = $validatedData['status_valid'];
    //         $tugasAkhir->save();

    //         $statusMessage = $validatedData['status_valid'] == 1 ? 'valid' : 'invalid';
    //         return ApiResponse::success(new TugasAkhirResource($tugasAkhir), "Tugas Akhir marked as $statusMessage");
    //     } catch (ValidationException $e) {
    //         return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
    //     } catch (Exception $e) {
    //         return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
    //     }
    // }
}
