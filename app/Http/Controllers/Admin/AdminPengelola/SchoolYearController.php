<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\SchoolYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolYearController extends Controller
{
    public function showSchoolYear(Request $request) {
        $query = SchoolYear::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'no' => 'id',
            'name' => 'name',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'created_at' => 'created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $schoolYears = $query->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Tahun Ajaran',
            'data' => $schoolYears,
        ]);
    }

    public function createSchoolYear(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        if ($endDate->lt($startDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            ], 422);
        }

        SchoolYear::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(['message' => 'tahun ajaran berhasil ditambahkan']);

    }

    public function findSchoolYear($id) {
        $school_year = SchoolYear::find($id);
        if ($school_year) {
            return response()->json($school_year);
        } else {
            return response()->json(['message' => 'tahun ajaran tidak ditemukan'], 404);
        }
    }

    public function editSchoolYear(Request $request, $id) {
        $school_year = SchoolYear::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        if ($endDate->lt($startDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        $school_year->update($data);

        return response()->json(['message' => 'lantai berhasil diupdate'], 200);
    }

    public function deleteSchoolYear($id) {
        $school_year = SchoolYear::findOrFail($id);

        if (!$school_year) {
            return response()->json(['message' => 'tahun ajaran tidak ditemukan'], 404);
        }

        $school_year->delete();

        return response()->json(['message' => 'tahun ajaran berhasil dihapus']);
    }
}