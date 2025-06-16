<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\RfidTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RfidTagController extends Controller
{
    // List RFID dengan pagination dan search
    public function getRfid(Request $request)
    {
        $query = RfidTag::with(['mahasiswa', 'dosen']);

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('rfid_code', 'like', "%{$searchTerm}%")
                  ->orWhereHas('mahasiswa', function($q2) use ($searchTerm) {
                      $q2->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('nim', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('dosen', function($q3) use ($searchTerm) {
                      $q3->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('nik', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $rfid = $query->get();

        $paginatedData = PaginationHelper::paginate($rfid, $perPage, $page, [
            'path' => $request->url(),  
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data RFID',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    // Detail RFID berdasarkan ID
    public function getRfidById($id)
    {
        $rfid = RfidTag::with(['mahasiswa', 'dosen'])->find($id);

        if (!$rfid) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Tag not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail RFID Tag',
            'data' => $rfid,
        ], 200);
    }

    // Create RFID baru
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid_code' => 'required|string|max:50|unique:rfid_tags,rfid_code',
            'id_student' => 'nullable|exists:mahasiswa,id',
            'id_dosen' => 'nullable|exists:dosen,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (($request->filled('id_student') && $request->filled('id_dosen')) ||
            (!$request->filled('id_student') && !$request->filled('id_dosen'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harus memilih salah satu: id_student atau id_dosen (bukan keduanya atau tidak sama sekali)',
            ], 422);
        }

        try {
            $rfid = RfidTag::create([
                'rfid_code' => $request->rfid_code,
                'id_student' => $request->id_student,
                'id_dosen' => $request->id_dosen,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'RFID Tag created successfully',
                'data' => $rfid,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Create RFID Tag error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update RFID berdasarkan ID
    public function updateRfid(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rfid_code' => "required|string|max:50|unique:rfid_tags,rfid_code,{$id}",
            'id_student' => 'nullable|exists:mahasiswa,id',
            'id_dosen' => 'nullable|exists:dosen,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (($request->filled('id_student') && $request->filled('id_dosen')) ||
            (!$request->filled('id_student') && !$request->filled('id_dosen'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harus memilih salah satu: id_student atau id_dosen (bukan keduanya atau tidak sama sekali)',
            ], 422);
        }

        $rfid = RfidTag::find($id);

        if (!$rfid) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Tag not found',
            ], 404);
        }

        try {
            $rfid->update([
                'rfid_code' => $request->rfid_code,
                'id_student' => $request->id_student,
                'id_dosen' => $request->id_dosen,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'RFID Tag updated successfully',
                'data' => $rfid,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update RFID Tag error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete RFID berdasarkan ID
    public function deleteRfid($id)
    {
        $rfid = RfidTag::find($id);

        if (!$rfid) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Tag not found',
            ], 404);
        }

        try {
            $rfid->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'RFID Tag deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Delete RFID Tag error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
