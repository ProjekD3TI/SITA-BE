<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\JenisInventaris;
use App\Models\AdminPengelola\Merk;
use App\Models\AdminPengelola\Ruang;
use App\Models\AdminPengelola\Tipe;
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
        }

        $query->orderBy('created_at', 'desc');

        if (!$request->has('search') || empty($request->search)) {
            $mahasiswa = $query->limit(10)->get();
        } else {
            $mahasiswa = $query->get();
        }
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($mahasiswa, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Mahasiswa',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    
    public function getDosen(Request $request) {
        $query = Dosen::where('status', 1);
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('nik', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if (!$request->has('search') || empty($request->search)) {
            $dosen = $query->limit(10)->get();
        } else {
            $dosen = $query->get();
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($dosen, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    public function getRuang(Request $request) {
        $query = Ruang::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_ruang', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if (!$request->has('search') || empty($request->search)) {
            $ruang = $query->limit(10)->get();
        } else {
            $ruang = $query->get();
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($ruang, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    public function getMerk(Request $request) {
        $query = Merk::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_merk', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if (!$request->has('search') || empty($request->search)) {
            $merk = $query->limit(10)->get();
        } else {
            $merk = $query->get();
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($merk, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    public function getTipe(Request $request) {
        $query = Tipe::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_tipe', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        if (!$request->has('search') || empty($request->search)) {
            $tipe = $query->limit(10)->get();
        } else {
            $tipe = $query->get();
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($tipe, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    
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
    public function getJenisInventaris(Request $request) {
    $query = JenisInventaris::query()
        ->with(['merk', 'tipe'])
        ->select(
            'jenis_inventaris.*', 
            'merk.nama_merk', 
            'tipe.nama_tipe'
        )
        ->join('merk', 'jenis_inventaris.id_merk', '=', 'merk.id_merk')
        ->join('tipe', 'jenis_inventaris.id_tipe', '=', 'tipe.id_tipe');

    if ($request->has('search') && !empty($request->search)) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('jenis_inventaris.nama_inventaris', 'like', "%{$searchTerm}%")
              ->orWhere('merk.nama_merk', 'like', "%{$searchTerm}%")
              ->orWhere('tipe.nama_tipe', 'like', "%{$searchTerm}%");
        });
    }

    $query->orderBy('jenis_inventaris.created_at', 'desc');

    if (!$request->has('search') || empty($request->search)) {
        $jenisInventaris = $query->limit(10)->get();
    } else {
        $jenisInventaris = $query->get();
    }
    
    $jenisInventaris->transform(function($item) {
        return [
            'id' => $item->id_jenis_inventaris, 
            'name' => $item->nama_tipe . ' ' . $item->nama_merk . ' - ' . $item->nama_inventaris,
            'id_jenis_inventaris' => $item->id_jenis_inventaris,
            'nama_inventaris' => $item->nama_inventaris,
            'nama_merk' => $item->nama_merk,
            'nama_tipe' => $item->nama_tipe,
            'merk' => $item->merk,
            'tipe' => $item->tipe,
        ];
    });

    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $paginatedData = PaginationHelper::paginate($jenisInventaris, $perPage, $page, [
        'path' => $request->url(),
        'query' => $request->query(),
    ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Jenis Inventaris',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }
    public function getInventaris(Request $request) {
    
    $query = Inventaris::with('jenisInventaris')
        ->where('kondisi', 'baik')
        ->where('status', 1);

    if ($request->has('search') && !empty($request->search)) {
        $searchTerm = $request->search;
        
        $query->where(function($q) use ($searchTerm) {
            $q->where('no_inventaris', 'like', "%{$searchTerm}%")
            ->orWhereHas('jenisInventaris', function($jq) use ($searchTerm) {
                $jq->where('nama_inventaris', 'like', "%{$searchTerm}%");
            });
        });
        
        $query->orderByRaw("CASE WHEN no_inventaris = ? THEN 0 ELSE 1 END", [$searchTerm])
              ->orderBy('created_at', 'desc');
        
        $inventaris = $query->get();
        
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data Inventaris',
            'data' => $inventaris,
            'pagination' => [
                'total' => $inventaris->count(),
                'per_page' => $inventaris->count(),
                'current_page' => 1,
                'last_page' => 1,
            ]
        ], 200);
        
    } else {
        $query->orderBy('created_at', 'desc');
        $inventaris = $query->limit(10)->get();
        
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
}
