<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    public function getSession(Request $request) 
    {
        $query = Session::query();
        
        if($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name_session', 'like', "%{$searchTerm}%")
                  ->orWhere('start_time', 'like', "%{$searchTerm}%")
                  ->orWhere('end_time', 'like', "%{$searchTerm}%");
            });
        }
        
        $query->orderBy('created_at', 'desc');
        
        if ($request->has('all') && $request->all == 'true') {
            $sessions = $query->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Semua data sesi',
                'data' => $sessions,
                'total' => $sessions->count()
            ], 200);
        } else {
            $sessions = $query->get();

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $paginatedData = PaginationHelper::paginate($sessions, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data Sesi',
                'data' => $paginatedData->items(),
                'pagination' => [
                    'total' => $paginatedData->total(),
                    'per_page' => $paginatedData->perPage(),
                    'current_page' => $paginatedData->currentPage(),
                    'last_page' => $paginatedData->lastPage(),
                ]
            ], 200);
        }
    }

    public function createSession(Request $request) {
        $validator = Validator::make($request->all(), [
            'name_session' => 'required',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $start = strtotime($request->start_time);
        $end = strtotime($request->end_time);
        
        if ($end <= $start) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => [
                    'end_time' => ['Waktu selesai harus lebih dari waktu mulai.']
                ],
            ], 422);
        }
        
        $session = Session::create([
            'name_session' => $request->name_session,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi created successfully',
            'data' => $session,
        ], 201);
    }

    public function getSessionById($id) {
        $session = Session::find($id);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Session details',
            'data' => $session,
        ], 200);
    }

    public function updateSession(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name_session' => 'required',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $start = strtotime($request->start_time);
        $end = strtotime($request->end_time);
        
        if ($end <= $start) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => [
                    'end_time' => ['Waktu selesai harus lebih dari waktu mulai.']
                ],
            ], 422);
        }

        $session = Session::findOrFail($id);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session not found',
            ], 404);
        }

        $session->update([
            'name_session' => $request->name_session,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Session updated successfully',
            'data' => $session,
        ], 200);
    }

    public function deleteSession($id) {
        $session = Session::findOrFail($id);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session not found',
            ], 404);
        }

        $session->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Session deleted successfully',
        ], 200);
    }

}
