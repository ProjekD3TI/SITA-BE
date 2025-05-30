<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Http\Controllers\Controller;
use App\Models\Dosen; 
use Illuminate\Http\Request;

class DosenAdminController extends Controller
{
    public function index()
    {
        // Ambil semua user yang rolenya dosen
        $dosen = Dosen::all();

        return response()->json([
            'success' => true,
            'data' => $dosen,
        ]);
    }
    public function show($id)
    {
        $dosen = Dosen::find($id);
        if (!$dosen) {
            return response()->json(['message' => 'Dosen not found'], 404);
        }
        return response()->json($dosen);
    }

}
