<?php

namespace App\Http\Controllers\RFID;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\RfidTag;
use App\Models\User;
use App\Models\Dosen;
use App\Models\AccessLog;
use App\Models\Booking;
use App\Models\AdminPengelola\Session;
use App\Models\AdminPengelola\FixedSchedule;
use App\Models\AdminPengelola\FixedScheduleSession;
use App\Models\AdminPengelola\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    // Ambil data log akses (GET)
    public function getLogs(Request $request)
    {
        $query = AccessLog::with(['ruang', 'mahasiswa', 'dosen'])->latest();
    
        // Kalau ada filter id_ruang, tambahkan kondisi where
        if ($request->has('id_ruang') && $request->id_ruang !== '') {
            $query->where('id_ruang', $request->id_ruang);
        }
    
        $logs = $query->get()->map(function ($log) {
            return [
                'nama' => $log->id_mahasiswa
                            ? optional($log->mahasiswa)->name
                            : optional($log->dosen)->name,
                'id_ruang' => $log->id_ruang,
                'nama_ruang' => optional($log->ruang)->nama ?? '-',
                'access_time' => $log->access_time,
                'status' => $log->access_status,
                'reason' => $log->reason,
            ];
        });
    
        return response()->json($logs);
    }
    
    // Log akses
    public function log(Request $request)
    {
        $uid = $request->uid;
        $status = $request->status; // 'granted' atau 'denied'
        $ruangId = $request->ruang_id;
        $waktu = $request->waktu ?? Carbon::now(); // fallback ke sekarang jika null
        $reason = $request->reason ?? null;

        $tag = RfidTag::where('rfid_code', $uid)->first();
        $nama = '-';
        $role = 'unknown';
        $idMahasiswa = null;
        $idDosen = null;

        if ($tag) {
            if ($tag->id_student) {
                $mahasiswa = User::find($tag->id_student);
                $nama = $mahasiswa?->name ?? '-';
                $role = 'mahasiswa';
                $idMahasiswa = $tag->id_student;
            } elseif ($tag->id_dosen) {
                $dosen = Dosen::find($tag->id_dosen);
                $nama = $dosen?->name ?? '-';
                $role = 'dosen';
                $idDosen = $tag->id_dosen;
            }
        }

        AccessLog::create([
            // 'nama' => $nama,
            'role' => $role,
            'rfid_code' => $uid,
            'access_status' => $status,
            'reason' => $reason,
            'id_ruang' => $ruangId,
            'access_time' => $waktu,
            'id_mahasiswa' => $idMahasiswa,
            'id_dosen' => $idDosen,
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
            return response()->json([
                'allowed' => false,
                'nama' => 'Tidak dikenal',
                'reason' => 'RFID tidak terdaftar'
            ]);
        }
    
        // Dosen selalu boleh masuk
        if ($tag->id_dosen) {
            $dosen = Dosen::find($tag->id_dosen);
            return response()->json([
                'allowed' => true,
                'nama' => $dosen?->name ?? 'Dosen',
                'role' => 'dosen',
                'reason' => 'Dosen diizinkan mengakses ruang'
            ]);
        }
    
        // Mahasiswa
        $mahasiswaId = $tag->id_student;
        $mahasiswa = User::find($mahasiswaId);
    
        $now = Carbon::now();
        $day = $now->dayOfWeekIso; // 1 = Senin, 7 = Minggu
        $time = $now->format('H:i:s');
        $date = $now->toDateString();
    
        // Ambil semua kelas aktif mahasiswa
        $idKelasList = Semester::where('id_student', $mahasiswaId)->pluck('id_class')->toArray();
    
        // Check booking valid
        $bookingAllowed = Booking::where('id_student', $mahasiswaId)
            ->where('id_ruang', $ruangId)
            ->where('date', $date)
            ->where('status', 'approved')
            ->get()
            ->filter(function ($booking) use ($time) {
                $start = Session::find($booking->start_session);
                $end = Session::find($booking->end_session);
                return $start && $end && $time >= $start->start_time && $time <= $end->end_time;
            })
            ->isNotEmpty();
    
        // Check jadwal tetap valid dari semua kelas
        $jadwalTetap = FixedSchedule::whereIn('id_class', $idKelasList)
            ->where('id_ruang', $ruangId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get()
            ->filter(function ($jadwal) use ($day, $time) {
                $sessions = FixedScheduleSession::where('id_fixed_schedule', $jadwal->id)
                ->where('day_of_week', $day)
                ->get();
            
            foreach ($sessions as $session) {
                $sessionTime = Session::find($session->id_session);
                if ($sessionTime && $time >= $sessionTime->start_time && $time <= $sessionTime->end_time) {
                    return true;
                }
            }
            
            return false;
            
            })
            ->isNotEmpty();
    
        $allowed = $bookingAllowed || $jadwalTetap;
    
        return response()->json([
            'allowed' => $allowed,
            'nama' => $mahasiswa?->name ?? 'Mahasiswa',
            'role' => 'mahasiswa',
            'reason' => !$allowed
                ? 'Tidak ada booking atau jadwal tetap yang sesuai'
                : ($bookingAllowed && $jadwalTetap
                    ? 'Akses diizinkan karena booking dan jadwal tetap'
                    : ($bookingAllowed
                        ? 'Akses diizinkan karena booking'
                        : 'Akses diizinkan karena jadwal tetap'))
        ]);
        
    }
    
}
