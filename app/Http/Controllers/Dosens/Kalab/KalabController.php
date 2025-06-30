<?php

namespace App\Http\Controllers\Dosens\Kalab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class KalabController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'jumlah_peminjaman' => Booking::count(),
            'jumlah_peminjaman_acc' => Booking::where('status', 'approved')->count(),
            'jumlah_peminjaman_pending' => Booking::where('status', 'pending')->count(),
            'jumlah_peminjaman_ditolak' => Booking::where('status','rejected')->count(),
        ]);
    }
}
