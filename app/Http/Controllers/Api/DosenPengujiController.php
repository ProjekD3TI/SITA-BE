<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;
use App\Http\Resources\DosenPengujiResource;

class DosenPengujiController extends Controller
{
    public function getReadyTA() {
        try {
            // Eager load the 'bimbingan' relationship and filter TugasAkhir with bimbingan count >= 9 where status_verifikasi = 1
            $requests = TugasAkhir::withCount(['bimbingan' => function ($query) {
                $query->where('status_verifikasi', 1);
            }])
            ->having('bimbingan_count', '>=', 9)
            ->get();

            return ApiResponse::success(TugasAkhirResource::collection($requests));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'list_dosen_id' => 'required|array|size:3',
                'list_dosen_id.*' => 'required|exists:dosens,id',
                'tugas_akhir_id' => 'required|exists:tugas_akhirs,id',
            ], [
                'list_dosen_id.size' => 'Exactly 3 dosen must be provided.',
            ]);

            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->find($request->tugas_akhir_id);

            // Check if the number of DosenPenguji for the TugasAkhir is less than 3
            if ($tugasAkhir->dosenPenguji()->count() >= 3) {
                return ApiResponse::error('Maximum of 3 DosenPenguji allowed for this TugasAkhir', 400);
            }

            // Ensure that one of the list_dosen_id is the DosenPembimbing
            if (!in_array($tugasAkhir->dosen_pembimbing_id, $request->list_dosen_id)) {
                return ApiResponse::error('One of the DosenPenguji must be the DosenPembimbing', 400);
            }

            // Check if any of the dosen_id already exists as DosenPenguji for the given TugasAkhir
            $existingDosenPenguji = DosenPenguji::where('tugas_akhir_id', $request->tugas_akhir_id)
                ->whereIn('dosen_id', $request->list_dosen_id)
                ->exists();

            if ($existingDosenPenguji) {
                return ApiResponse::error('One or more of the selected dosen are already assigned as DosenPenguji for this TugasAkhir', 400);
            }

            $dosenPengujiData = [];
            foreach ($request->list_dosen_id as $dosenId) {
                $dosenPengujiData[] = [
                    'dosen_id' => $dosenId,
                    'tugas_akhir_id' => $request->tugas_akhir_id,
                    'is_dosen_pembimbing' => $dosenId == $tugasAkhir->dosen_pembimbing_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DosenPenguji::insert($dosenPengujiData);

            $dosenPenguji = DosenPenguji::with(['tugasAkhir', 'dosen'])->where('tugas_akhir_id', $request->tugas_akhir_id)->get();

            return ApiResponse::success(DosenPengujiResource::collection($dosenPenguji), 'DosenPenguji created successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}
