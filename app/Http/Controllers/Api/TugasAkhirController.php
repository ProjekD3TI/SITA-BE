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
        // Validate the request
        $request->validate([
            'status_aktif' => 'required|in:0,2',
        ]);

        if ($request->input('status_aktif') == 1) {
            return response()->json(['message' => 'Status cannot be set to 1'], 400);
        }

        // Find the record by ID
        $tugasAkhir = TugasAkhir::find($id);

        if (!$tugasAkhir) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Update the status_active
        $tugasAkhir->status_aktif = $request->input('status_aktif');
        $tugasAkhir->save();

        return response()->json(['message' => 'Status updated successfully'], 200);
    }
}
