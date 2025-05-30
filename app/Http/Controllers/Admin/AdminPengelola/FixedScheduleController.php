<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Classes;
use App\Models\AdminPengelola\FixedSchedule;
use App\Models\AdminPengelola\FixedScheduleSession;
use App\Models\AdminPengelola\Ruang;
use App\Models\AdminPengelola\Session;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FixedScheduleController extends Controller
{
    public function getSchedule(Request $request)
    {
        $query = FixedSchedule::query();

        if($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('pemakaian', 'like', "%{$searchTerm}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$searchTerm}%");
            });
        }
        $query->orderBy('created_at', 'desc');
        $schedules = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginatedData = PaginationHelper::paginate($schedules, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Jadwal Tetap',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createSchedule(Request $request) {
    $validator = Validator::make($request->all(), [
        'id_ruang' => 'required|exists:ruangs,id_ruang',
        'pemakaian' => 'required|string|max:255',
        'id_class' => 'required',
        'penanggung_jawab' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'sessions' => 'required|array|min:1',
        'sessions.*.day_of_week' => 'required|integer|between:1,7',
        'sessions.*.id_session' => 'required|exists:sessions,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422);
    }

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);

    if ($endDate->lt($startDate)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ], 422);
    }

    // check kelas dan ruang apakah ada
    $ruang = Ruang::find($request->id_ruang);
    if (!$ruang) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ruang tidak ditemukan',
        ], 422);
    }
    $kelas = Classes::find($request->id_class);
    if (!$kelas) {
        return response()->json([
            'status' => 'error',
            'message' => 'Kelas tidak ditemukan',
        ], 422);
    }

    $conflictingSessions = [];

     // verify bahwa sesi yang dipilih ada
    foreach ($request->sessions as $sessionData) {
        $session = Session::find($sessionData['id_session']);
            
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak ditemukan',
            ], 422);
        }

        // Cek konflik: apakah sesi ini sudah dipakai di ruangan & hari yang sama
        $conflict = FixedScheduleSession::whereHas('fixedSchedule', function ($query) use ($request) {
                $query->where('id_ruang', $request->id_ruang);
            })
            ->where('day_of_week', $sessionData['day_of_week'])
            ->where('id_session', $sessionData['id_session'])
            ->exists();

        if ($conflict) {
            // Tambahkan sesi yang konflik ke array
            $conflictingSessions[] = $sessionData['id_session'];
        }
    }
    // Jika ada konflik, tampilkan semua sesi yang konflik sekaligus
    if (!empty($conflictingSessions)) {
        $sessionsText = implode(',', $conflictingSessions);
        
        return response()->json([
            'status' => 'error',
            'message' => "Konflik sesi: Ruangan {$ruang->nama_ruang} sudah digunakan pada sesi $sessionsText"
        ], 422);
    }
    

    try {
        DB::beginTransaction();

       // Buat jadwal tetap
        $fixedSchedule = FixedSchedule::create([
            'id_ruang' => $request->id_ruang,
            'pemakaian' => $request->pemakaian,
            'id_class' => $request->id_class,
            'penanggung_jawab' => $request->penanggung_jawab,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Buat fix jadwal sesi otomatis
        $fixedScheduleSessions = [];
        foreach ($request->sessions as $sessionData) {
            $scheduleSession = FixedScheduleSession::create([
                'id_fixed_schedule' => $fixedSchedule->id,
                'id_session' => $sessionData['id_session'],
                'day_of_week' => $sessionData['day_of_week'],
            ]);
            $fixedScheduleSessions[] = $scheduleSession;
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal tetap berhasil dibuat',
            'data' => [
                'fixed_schedule' => $fixedSchedule,
                'sessions' => $fixedScheduleSessions
            ]
        ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat jadwal tetap',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getScheduleById($id) {
        $schedule = FixedSchedule::with(['sessions.session'])->find($id);
        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal Tetap Tidak Ditemukan',
            ], 404);
        }
        
        // Get room and class details
        $ruang = Ruang::find($schedule->id_ruang);
        $kelas = Classes::find($schedule->id_class);
        
        $scheduleData = $schedule->toArray();
        $scheduleData['ruang'] = $ruang ? $ruang->nama_ruang : null;
        $scheduleData['kelas'] = $kelas ? $kelas->name : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Detail Jadwal Tetap',
            'data' => $scheduleData,
        ], 200);
    }

    public function updateSchedule(Request $request, $id) {
    $validator = Validator::make($request->all(), [
        'id_ruang' => 'required|exists:ruangs,id_ruang',
        'pemakaian' => 'required|string|max:255',
        'id_class' => 'required',
        'penanggung_jawab' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'sessions' => 'required|array|min:1',
        'sessions.*.day_of_week' => 'required|integer|between:1,7',
        'sessions.*.id_session' => 'required|exists:sessions,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422);
    }

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);

    if ($endDate->lt($startDate)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ], 422);
    }

    $schedule = FixedSchedule::find($id);
    if (!$schedule) {
        return response()->json([
            'status' => 'error',
            'message' => 'Jadwal Tetap Tidak Ditemukan',
        ], 404);
    }

    // check kelas dan ruang apakah ada
    $ruang = Ruang::find($request->id_ruang);
    if (!$ruang) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ruang tidak ditemukan',
        ], 422);
    }
    
    $kelas = Classes::find($request->id_class);
    if (!$kelas) {
        return response()->json([
            'status' => 'error',
            'message' => 'Kelas tidak ditemukan',
        ], 422);
    }

    $conflictingSessions = [];

    // verify bahwa sesi yang dipilih ada
    foreach ($request->sessions as $sessionData) {
        $session = Session::find($sessionData['id_session']);
            
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak ditemukan',
            ], 422);
        }

         // Cek konflik sesi pada ruangan & hari yang sama, kecuali jadwal ini sendiri
        $conflict = FixedScheduleSession::whereHas('fixedSchedule', function ($query) use ($request, $id) {
                $query->where('id_ruang', $request->id_ruang)
                    ->where('id', '!=', $id); // abaikan jadwal ini sendiri
            })
            ->where('day_of_week', $sessionData['day_of_week'])
            ->where('id_session', $sessionData['id_session'])
            ->exists();

        if ($conflict) {
            // Tambahkan sesi yang konflik ke array
            $conflictingSessions[] = $sessionData['id_session'];
        }
    }

    if (!empty($conflictingSessions)) {
        $sessionsText = implode(',', $conflictingSessions);
        
        return response()->json([
            'status' => 'error',
            'message' => "Konflik sesi: Ruangan {$ruang->nama_ruang} sudah digunakan pada sesi $sessionsText"
        ], 422);
    }

    try {
        DB::beginTransaction();

        // Update jadwal tetap
        $schedule->update([
            'id_ruang' => $request->id_ruang,
            'pemakaian' => $request->pemakaian,
            'id_class' => $request->id_class,
            'penanggung_jawab' => $request->penanggung_jawab,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Hapus semua sesi lama
        FixedScheduleSession::where('id_fixed_schedule', $id)->delete();

        // Buat sesi baru
        $fixedScheduleSessions = [];
        foreach ($request->sessions as $sessionData) {
            $scheduleSession = FixedScheduleSession::create([
                'id_fixed_schedule' => $schedule->id,
                'id_session' => $sessionData['id_session'],
                'day_of_week' => $sessionData['day_of_week'],
            ]);
            $fixedScheduleSessions[] = $scheduleSession;
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal Tetap Berhasil Diperbarui',
            'data' => [
                'fixed_schedule' => $schedule,
                'sessions' => $fixedScheduleSessions
            ]
        ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui jadwal tetap',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSchedule($id) {
        try {
            $schedule = FixedSchedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal Tetap Tidak Ditemukan',
                ], 404);
            }

            DB::beginTransaction();
            
            // Delete all associated sessions first
            FixedScheduleSession::where('id_fixed_schedule', $id)->delete();
            
            // Delete main schedule
            $schedule->delete();
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal Tetap Berhasil Dihapus',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus jadwal tetap',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

}
