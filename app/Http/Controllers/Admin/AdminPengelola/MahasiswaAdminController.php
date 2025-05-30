<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;

class MahasiswaAdminController extends Controller
{
    public function index()
    {
        // Ambil semua user yang rolenya mahasiswa
        $mahasiswa = User::all();

        return response()->json([
            'success' => true,
            'data' => $mahasiswa,
        ]);
    }
    public function show($id)
    {
        $mahasiswa = User::find($id);
        if (!$mahasiswa) {
            return response()->json(['message' => 'Mahasiswa not found'], 404);
        }
        return response()->json($mahasiswa);
    }

}
