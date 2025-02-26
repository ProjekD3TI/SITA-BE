<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPengelolaController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Admin Pengelola Dashboard',
        ]);
    }
}
