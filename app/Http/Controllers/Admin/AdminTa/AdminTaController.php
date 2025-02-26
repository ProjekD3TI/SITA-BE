<?php

namespace App\Http\Controllers\Admin\AdminTa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminTaController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Admin TA Dashboard',
        ]);
    }
}
