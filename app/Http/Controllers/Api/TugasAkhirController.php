<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;

class TugasAkhirController extends Controller
{
    public function index(){
        try {
            $requests = TugasAkhir::all();
            return ApiResponse::success(TugasAkhirResource::collection($requests));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function updateActiveStatus(Request $request, $id)
    {
        $request->validate([
            'status_aktif' => 'required|in:0,1,2',
        ]);

        // if ($request->input('status_aktif') == 1) {
        //     return ApiResponse::error('Status cannot be set to 1', 400);
        // }

        // Find the record by ID
        $tugasAkhir = TugasAkhir::find($id);

        if (!$tugasAkhir) {
            return ApiResponse::error('Record not found', 404);
        }

        // Update the status_active
        $tugasAkhir->status_aktif = $request->input('status_aktif');
        $tugasAkhir->save();

        return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Status updated successfully');
    }
}
