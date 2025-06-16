<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminPengelola\RfidTag;
use App\Models\AdminPengelola\Mahasiswa;
use App\Models\AdminPengelola\Dosen;
use App\Models\AdminPengelola\LogAkses;
use App\Models\AdminPengelola\Booking;
use App\Models\AdminPengelola\Session;
use Carbon\Carbon;

class AccessController extends Controller
{
    // Simpan log akses
    public function log(Request $request)
    {
        $uid = $request->uid;
        $status = $request->status;
        $ruangId = $request->ruang_id;
        $waktu = $request->waktu;

        $tag = RfidTag::where('rfid_code', $uid)->first();
        $nama = '-';
        $role = 'unknown';

        if ($tag) {
            if ($tag->id_student) {
                $mahasiswa = Mahasiswa::find($tag->id_student);
                $nama = $mahasiswa?->name ?? '-';
                $role = 'mahasiswa';
            } elseif ($tag->id_dosen) {
                $dosen = Dosen::find($tag->id_dosen);
                $nama = $dosen?->name ?? '-';
                $role = 'dosen';
            }
        }

        LogAkses::create([
            'nama' => $nama,
            'role' => $role,
            'status' => $status,
            'ruang_id' => $ruangId,
            'waktu' => $waktu,
        ]);

        return response()->json(['success' => true]);
    }

    // Cek apakah akses diperbolehkan
    public function check(Request $request)
    {
        $uid = $request->uid;
        $ruangId = $request->ruang_id;

        $tag = RfidTag::where('rfid_code', $uid)->first();
        if (!$tag) {
            return response()->json(['allowed' => false, 'nama' => 'Tidak dikenal']);
        }

        // Jika tag untuk dosen, akses selalu allowed
        if ($tag->id_dosen) {
            $dosen = Dosen::find($tag->id_dosen);
            return response()->json(['allowed' => true, 'nama' => $dosen?->name ?? 'Dosen']);
        }

        // Untuk mahasiswa, cek booking dan jadwal
        $mahasiswaId = $tag->id_student;
        $mahasiswa = Mahasiswa::find($mahasiswaId);

        $now = Carbon::now();
        $day = $now->dayOfWeekIso; // 1 (Senin) s/d 7 (Minggu)
        $time = $now->format('H:i:s');

        $allowed = Booking::where('id_student', $mahasiswaId)
            ->where('id_ruang', $ruangId)
            ->where('date', $now->toDateString())
            ->where('status', 'approved')
            ->get()
            ->filter(function ($booking) use ($time) {
                $startSession = Session::find($booking->start_session);
                $endSession = Session::find($booking->end_session);

                if (!$startSession || !$endSession) {
                    return false;
                }

                return $time >= $startSession->start_time && $time <= $endSession->end_time;
            })
            ->isNotEmpty();

        return response()->json([
            'allowed' => $allowed,
            'nama' => $mahasiswa?->name ?? 'Mahasiswa'
        ]);
    }
}
