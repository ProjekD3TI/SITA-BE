<?php

namespace App\Http\Controllers\Dosens\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Petugas Dashboard',
        ]);
    }
}
