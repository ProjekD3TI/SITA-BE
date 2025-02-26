<?php

namespace App\Http\Controllers\Dosens\Kalab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KalabController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Kalab Dashboard',
        ]);
    }
}
