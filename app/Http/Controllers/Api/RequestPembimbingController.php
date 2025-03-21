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

class RequestPembimbingController extends Controller
{
    // public function index()
    // {
    //     try {
    //         // Eager loading relationships
    //         $requests = RequestPembimbing::with(['tugasAkhir', 'dosen'])->get();
    //         return ApiResponse::success(RequestPembimbingResource::collection($requests));
    //     } catch (Exception $e) {
    //         return ApiResponse::error($e->getMessage(), 500);
    //     }
    // }

    public function getByDosen()  {
        $dosenId = Auth::user()->id;
        try {
            // Eager loading relationships
            $requests = RequestPembimbing::with(['tugasAkhir', 'dosen'])
                ->where('dosen_id', $dosenId)
                ->get();
            return ApiResponse::success(RequestPembimbingResource::collection($requests));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function storeByAdmin(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tugas_akhir_id' => 'required|integer',
                'dosen_id' => 'required|integer',
            ]);

            // Lazy eager loading
            $tugasAkhir = TugasAkhir::find($validatedData['tugas_akhir_id']);
            if ($tugasAkhir->dosen_pembimbing_id != null) {
                return ApiResponse::error('Mahasiswa already has an Pembimbing', 400);
            }

            $requestPembimbing = RequestPembimbing::create([
                'tugas_akhir_id' => $validatedData['tugas_akhir_id'],
                'dosen_id' => $validatedData['dosen_id'],
                'tanggal_respon' => null,
                'status_request' => 1,
            ]);

            // Update dosen_pembimbing_id in TugasAkhir
            if ($tugasAkhir) {
                $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_id'];
                $tugasAkhir->save();
            }

            return ApiResponse::success(new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')), 'Request and Tugas Akhir created successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function storeWithTugasAkhir(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dosen_id' => 'required|integer',
            ]);

            // Get mahasiswa_id from the logged-in user
            $mahasiswa_id = Auth::id();

            // Check if the mahasiswa already has an active TugasAkhir
            $existingTugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa_id)
                ->where('status_aktif', 1)
                ->first();

            if ($existingTugasAkhir) {
                return ApiResponse::error('Mahasiswa already has an active TugasAkhir', 400);
            }

            // Create TugasAkhir
            $tugasAkhir = TugasAkhir::create([
                'mahasiswa_id' => $mahasiswa_id,
                'status_aktif' => 1,
            ]);

            // Create RequestPembimbing with tugas_akhir_id from TugasAkhir
            $requestPembimbing = RequestPembimbing::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'dosen_id' => $validatedData['dosen_id'],
                'status_request' => 0,
                'tanggal_respon' => null,
            ]);

            return ApiResponse::success(new RequestPembimbingResource($requestPembimbing->load('tugasAkhir', 'dosen')), 'Request and Tugas Akhir created successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function updateApprovalDosen(Request $request, $id)
    {
        try {
            $requestPembimbing = RequestPembimbing::find($id);

            if (!$requestPembimbing) {
                return ApiResponse::error('Request not found', 404);
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
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // public function destroy($id)
    // {
    //     try {
    //         $requestPembimbing = RequestPembimbing::find($id);

    //         if (!$requestPembimbing) {
    //             return ApiResponse::error('Request not found', 404);
    //         }

    //         $requestPembimbing->delete();

    //         return ApiResponse::success(null, 'Request deleted successfully');
    //     } catch (Exception $e) {
    //         return ApiResponse::error($e->getMessage(), 500);
    //     }
    // }
}
