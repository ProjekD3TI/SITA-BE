<?php

namespace App\Http\Controllers\Dosens\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DosenController extends Controller
{
    public function dashboard()
    {
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
            ->where('bookings.id_dosen', Auth::guard('dosen')->id())
            ->whereIn('bookings.status', ['pending', 'approved']) // bisa disesuaikan
            ->orderBy('bookings.created_at', 'desc')
            ->get();

        $peminjaman_ruang = $peminjaman_ruang->map(function ($item) {
            $startTime = $item->start_time ? date('H:i', strtotime($item->start_time)) : null;
            $endTime = $item->end_time ? date('H:i', strtotime($item->end_time)) : null;
            $date = $item->date ? date('d M Y', strtotime($item->date)) : null;

            $item->formatted_date = $date;
            $item->formatted_time = $startTime && $endTime ? "$startTime - $endTime" : null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Dosen',
            'data' => [
                'peminjaman_ruang' => $peminjaman_ruang,
            ],
        ]);
    }


    public function getProfileDosen(Request $request) {
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

    public function editProfileDosen(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'nik' => 'nullable',
            'password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // if ($request->name) {
        //     $user->name = $request->name;
        // }

        // if ($request->nik) {
        //     $user->nik = $request->nik;
        // }

        // if ($request->password) {
        //     $user->password = bcrypt($request->password);
        // }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        
        if ($request->filled('nik')) {
            $user->nik = $request->nik;
        }
        
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }
}
