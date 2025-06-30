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
        // $total_inventaris = Inventaris::count();
        // $total_bhp = Bhp::count();
        $total_ruang = Ruang::count();
        $total_gedung = Gedung::count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Admin Pengelola Dashboard Data',
            'data' => [
                // 'total_inventaris' => $total_inventaris,
                // 'total_bhp' => $total_bhp,
                'total_ruang' => $total_ruang,
                'total_gedung' => $total_gedung
            ]
        ], 200);
    }
}