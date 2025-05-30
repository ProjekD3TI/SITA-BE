<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SemesterController extends Controller
{
    public function getSemester(Request $request) {
        $query = Semester::query()
        ->leftJoin('mahasiswa', 'semester.id_student', '=', 'mahasiswa.id')
        ->leftJoin('classes', 'semester.id_class', '=', 'classes.id')
        ->select(
            'semester.*',
            'mahasiswa.name as student_name',
            'mahasiswa.nim as student_nim',
            'classes.name as class_name'
        );
        
    if($request->has('search') && !empty($request->search)) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('classes.name', 'like', "%{$searchTerm}%")
              ->orWhere('mahasiswa.name', 'like', "%{$searchTerm}%")
              ->orWhere('mahasiswa.nim', 'like', "%{$searchTerm}%");
        });
    }
    
    $query->orderBy('semester.created_at', 'desc');
    $semesters = $query->get();

    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $paginatedData = PaginationHelper::paginate($semesters, $perPage, $page, [
        'path' => $request->url(),
        'query' => $request->query(),
    ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Semester',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createSemester(Request $request) {

            // Deteksi apakah input berupa satu atau banyak mahasiswa
            $isBulk = is_array($request->id_student);
    
            // Validasi umum
            $rules = [
                'id_class' => 'required|exists:classes,id',
                'id_student' => $isBulk ? 'required|array|min:1' : 'required|exists:mahasiswa,id',
            ];
    
            if ($isBulk) {
                $rules['id_student.*'] = 'exists:mahasiswa,id';
            }
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            if ($isBulk) {
                $insertData = collect($request->id_student)->map(function ($studentId) use ($request) {
                    return [
                        'id_student' => $studentId,
                        'id_class' => $request->id_class,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
    
                // Optional: hilangkan duplikat sebelum insert
                $insertData = array_filter($insertData, function ($data) {
                    return !Semester::where('id_student', $data['id_student'])
                        ->where('id_class', $data['id_class'])
                        ->exists();
                });
    
                Semester::insert($insertData);
    
                return response()->json([
                    'status' => 'success',
                    'message' => count($insertData) . ' mahasiswa berhasil ditambahkan ke kelas.',
                ], 201);
            } else {
                // Single insert
                if (Semester::where('id_student', $request->id_student)->where('id_class', $request->id_class)->exists()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Mahasiswa sudah terdaftar di kelas ini.',
                    ], 409);
                }
    
                Semester::create([
                    'id_student' => $request->id_student,
                    'id_class' => $request->id_class,
                ]);
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Mahasiswa berhasil ditambahkan ke kelas.',
                ], 201);
            }
        }

    public function getSemesterById($id) {
        $semester = Semester::findOrFail($id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Semester details',
            'data' => $semester,
        ], 200);
    }

    public function updateSemester(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'id_student' => 'required|exists:mahasiswa,id',
            'id_class' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $semester = Semester::find($id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found',
            ], 404);
        }

        $semester->update([
            'id_student' => $request->id_student,
            'id_class' => $request->id_class,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Semester updated successfully',
            'data' => $semester,
        ], 200);
    }

    public function deleteSemester($id) {
        $semester = Semester::findOrFail($id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found',
            ], 404);
        }

        $semester->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Semester deleted successfully',
        ], 200);
    }
}
