<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Booking;
use App\Models\AdminPengelola\FixedSchedule;

class MahasiswaController extends Controller
{
    public function dashboard() {
        //menampilkan data peminjaman bhp dan inventaris user yang login dengan status peminjaman yang belum dikembalikan
    //      $peminjaman_bhp = PeminjamanBhp::where(function($q) {
    //         $q->where('id_peminjam', Auth::id())
    //           ->where('tipe_peminjam', 'mahasiswa')
    //           ->orWhere(function($subQ) {
    //               $subQ->where('id_peminjam', Auth::guard('dosen')->id())
    //                    ->where('tipe_peminjam', 'dosen');
    //           });
    //     })
    //     ->where('status_pengembalian', '!=', 'selesai')
    //     ->orderBy('created_at', 'desc')
    //     ->get();
    
    // $peminjaman_inventaris = PeminjamanInventaris::where(function($q) {
    //         $q->where('id_peminjam', Auth::id())
    //           ->where('tipe_peminjam', 'mahasiswa')
    //           ->orWhere(function($subQ) {
    //               $subQ->where('id_peminjam', Auth::guard('dosen')->id())
    //                    ->where('tipe_peminjam', 'dosen');
    //           });
    //     })
    //     ->where('status_peminjaman', '!=', 'selesai') 
    //     ->get();

    $peminjaman_ruang = Booking::query()
        ->leftJoin('ruangs', 'bookings.id_ruang', '=', 'ruangs.id_ruang')
        ->leftJoin('sessions as start_sessions', 'bookings.start_session', '=', 'start_sessions.id')
        ->leftJoin('sessions as end_sessions', 'bookings.end_session', '=', 'end_sessions.id')
        ->select(
            'bookings.*',
            'ruangs.nama_ruang',
            'start_sessions.start_time',
            'end_sessions.end_time'
        )
        ->where(function($q) {
            $q->where('bookings.id_student', Auth::guard('mahasiswa')->id())
              ->orWhere('bookings.id_dosen', Auth::guard('dosen')->id());
        })
        ->where('bookings.status', 'pending')
        ->orderBy('bookings.created_at', 'desc')
        ->get();

        $peminjaman_ruang = $peminjaman_ruang->map(function($item) {
            $startTime = $item->start_time ? date('H:i', strtotime($item->start_time)) : null;
            $endTime = $item->end_time ? date('H:i', strtotime($item->end_time)) : null;
            
            $date = $item->date ? date('d M Y', strtotime($item->date)) : null;
            
            $item->formatted_date = $date;
            $item->formatted_time = $startTime && $endTime ? "$startTime - $endTime" : null;
            
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Mahasiswa',
            'data' => [
                'peminjaman_ruang' => $peminjaman_ruang,
            ],
        ]);
    }

    public function getProfileMahasiswa(Request $request) {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User unauthenticated',
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function editProfileMahasiswa(Request $request) {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'no_telp' => 'nullable',
            'password' => 'nullable|min:8',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        
        if ($request->filled('no_telp')) {
            $user->no_telp = $request->no_telp;
        }
        
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile telah UPdate',
            'data' => $user,
        ]);
    }
}
