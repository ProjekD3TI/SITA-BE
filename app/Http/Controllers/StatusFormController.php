<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusFormResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\StatusForm;

class StatusFormController extends Controller
{

    public function getStatusFormByAdmin()
    {
        try {
            $statusForm = StatusForm::all();
            $data = StatusFormResource::collection($statusForm);
            return ApiResponse::success($data, 'Berhasil mendapatkan data status form', 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
    public function updateStatusFormByAdmin(Request $request, $idForm)
    {
        try {

            $request->validate([
                'is_open' => 'required|boolean',
            ]);

            $statusForm = StatusForm::findOrFail($idForm);
            if (!$statusForm) {
                return ApiResponse::error('Status Form tidak ditemukan', 404);
            }

            $statusForm->is_open = $request->is_open;
            $statusForm->save();

            return ApiResponse::success(StatusFormResource::make($statusForm), 'Status Form berhasil diperbarui', 200);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
