<?php

namespace App\Http\Controllers\Dosens\Kaprodi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KaprodiController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Kaprodi Dashboard',
        ]);
    }
}
