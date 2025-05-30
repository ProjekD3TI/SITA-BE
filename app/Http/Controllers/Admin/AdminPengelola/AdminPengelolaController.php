<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\Ruang;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPengelolaController extends Controller
{
    public function dashboard() {
        $total_inventaris = Inventaris::count();
        $total_bhp = Bhp::count();
        $total_ruang = Ruang::count();
        $total_gedung = Gedung::count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Admin Pengelola Dashboard Data',
            'data' => [
                'total_inventaris' => $total_inventaris,
                'total_bhp' => $total_bhp,
                'total_ruang' => $total_ruang,
                'total_gedung' => $total_gedung
            ]
        ], 200);
    }

    public function showBhp(Request $request) {
        $query = Bhp::query();
    
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_bhp', 'like', "%{$searchTerm}%")
                  ->orWhere('peruntukan', 'like', "%{$searchTerm}%");
            });
        }
    
        $query->orderBy('created_at', 'desc');
        
        if (!$request->has('search') || empty($request->search)) {
            $bhp = $query->limit(10)->get();
        } else {
            $bhp = $query->get();
        }
    
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
    
        $paginatedData = PaginationHelper::paginate($bhp, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Data BHP',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    // Method to get a specific BHP by ID
    public function getBhpById(Request $request, $id) {
        $bhp = Bhp::find($id);
        
        if (!$bhp) {
            return response()->json([
                'status' => 'error',
                'message' => 'BHP not found',
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'BHP Details',
            'data' => $bhp
        ], 200);
    }


    public function getMahasiswa(Request $request) {
        $query = User::where('status', 1);
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('nim', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
            
            // Return all results for search
            $mahasiswa = $query->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data Mahasiswa Search Results',
                'data' => $mahasiswa,
                'pagination' => [
                    'total' => count($mahasiswa),
                    'per_page' => count($mahasiswa),
                    'current_page' => 1,
                    'last_page' => 1,
                ]
            ], 200);
        } else {
            // For initial load, sort and paginate
            $query->orderBy('created_at', 'desc');
            $perPage = $request->input('per_page', 10);
            $mahasiswa = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data Mahasiswa',
                'data' => $mahasiswa->items(),
                'pagination' => [
                    'total' => $mahasiswa->total(),
                    'per_page' => $mahasiswa->perPage(),
                    'current_page' => $mahasiswa->currentPage(),
                    'last_page' => $mahasiswa->lastPage(),
                ]
            ], 200);
        }
    }
    
    // Updated getDosen method for both initial load and search
    public function getDosen(Request $request) {
        $query = Dosen::where('status', 1);
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('nik', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
            
            // Return all results for search
            $dosen = $query->get();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data Dosen Search Results',
                'data' => $dosen,
                'pagination' => [
                    'total' => count($dosen),
                    'per_page' => count($dosen),
                    'current_page' => 1,
                    'last_page' => 1,
                ]
            ], 200);
        } else {
            // For initial load, sort and paginate
            $query->orderBy('created_at', 'desc');
            $perPage = $request->input('per_page', 10);
            $dosen = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data Dosen',
                'data' => $dosen->items(),
                'pagination' => [
                    'total' => $dosen->total(),
                    'per_page' => $dosen->perPage(),
                    'current_page' => $dosen->currentPage(),
                    'last_page' => $dosen->lastPage(),
                ]
            ], 200);
        }
    }
    
    // Add methods to get specific mahasiswa or dosen by ID
    public function getMahasiswaById(Request $request, $id) {
        $mahasiswa = User::where('status', 1)->find($id);
        
        if (!$mahasiswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mahasiswa not found',
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Mahasiswa Details',
            'data' => $mahasiswa
        ], 200);
    }
    
    public function getDosenById(Request $request, $id) {
        $dosen = Dosen::where('status', 1)->find($id);
        
        if (!$dosen) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dosen not found',
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Dosen Details',
            'data' => $dosen
        ], 200);
    }

    // buat fetch di createpeminjaman
    public function getInventaris(Request $request) {
        // get inventaris dengan kondisi = baik
        $inventaris = Inventaris::where('kondisi', 'baik')->where('status', 1)->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($inventaris, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Inventaris',
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
