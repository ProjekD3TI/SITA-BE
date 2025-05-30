<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\AdminPengelola\FixedSchedule;
use App\Models\AdminPengelola\FixedScheduleSession;
use App\Models\AdminPengelola\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{

    public function getUserBooking(Request $request) {
        $query = Booking::query()
            ->leftJoin('ruangs', 'bookings.id_ruang', '=', 'ruangs.id_ruang')
            ->leftJoin('sessions', 'bookings.start_session', '=', 'sessions.id')
            ->select(
                'bookings.*',
                'ruangs.nama_ruang',
                'sessions.name_session'
            )
            ->where('bookings.id_student', auth()->id());

        if($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('ruangs.nama_ruang', 'like', "%{$searchTerm}%")
                ->orWhere('sessions.name_session', 'like', "%{$searchTerm}%");
            });
        }
        $query->orderBy('bookings.created_at', 'desc');
        $bookings = $query->get();
        
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginatedData = PaginationHelper::paginate($bookings, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Booking',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createBooking(Request $request) {
        $validator = Validator::make($request->all(), [
        'id_ruang' => 'required|exists:ruangs,id_ruang',
        'date' => 'required|date',
        'start_session' => 'required|exists:sessions,id',
        'end_session' => 'required|exists:sessions,id',
        'keterangan' => 'nullable|string',
        'files' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422);
    }

    $startSession = Session::find($request->start_session);
    $endSession = Session::find($request->end_session);

    if (!$startSession || !$endSession) {
        return response()->json([
            'status' => 'error',
            'message' => 'Sesi tidak ditemukan',
        ], 404);
    }
    
    if (strtotime($startSession->start_time) >= strtotime($endSession->start_time)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Sesi akhir harus setelah sesi awal',
            'details' => [
                'start_session' => [
                    'id' => $startSession->id,
                    'name' => $startSession->name_session,
                    'time' => $startSession->start_time . ' - ' . $startSession->end_time
                ],
                'end_session' => [
                    'id' => $endSession->id,
                    'name' => $endSession->name_session,
                    'time' => $endSession->start_time . ' - ' . $endSession->end_time
                ]
            ]
        ], 422);
    }

    // cek jika ada sesi di antara start dan end, pastikan sesi-sesi tersebut berurutan
    if ($request->start_session < $request->end_session) {
        $intermediateSessions = Session::whereBetween('id', [$request->start_session, $request->end_session])
            ->orderBy('start_time')
            ->get();
        
        // cek apakah jumlah sesi sesuai (tidak ada yang terlewat)
        if (count($intermediateSessions) != ($request->end_session - $request->start_session + 1)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi tidak berurutan, pastikan semua sesi yang dipilih tersedia',
            ], 422);
        }
        
        // cek apakah waktu sesi berurutan (end time sesi sebelumnya <= start time sesi berikutnya)
        for ($i = 0; $i < count($intermediateSessions) - 1; $i++) {
            $currentSession = $intermediateSessions[$i];
            $nextSession = $intermediateSessions[$i + 1];
            
            if (strtotime($currentSession->end_time) > strtotime($nextSession->start_time)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Sesi tidak berurutan waktu: {$currentSession->name_session} berakhir setelah {$nextSession->name_session} dimulai",
                ], 422);
            }
        }
    }

    $startTime = $startSession->start_time;
    $endTime = $endSession->end_time;

    // ambil semua sesi yang tumpang tindih dengan rentang waktu
    $includedSessions = Session::where(function ($query) use ($startTime, $endTime) {
        $query->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($q) use ($startTime, $endTime) {
                  $q->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime);
              });
    })->pluck('id');

    $dayOfWeek = date('N', strtotime($request->date)); // 1=Senin ... 7=Minggu

    // cek rentang tanggal semester aktif
    $activeSchedule = FixedSchedule::where('id_ruang', $request->id_ruang)
        ->where('start_date', '<=', $request->date)
        ->where('end_date', '>=', $request->date)
        ->first();

    if (!$activeSchedule) {
        return response()->json([
            'status' => 'error',
            'message' => 'Peminjaman di luar rentang semester/jadwal tetap',
        ], 422);
    }

    // cek konflik jadwal tetap
    $conflictFixed = FixedScheduleSession::where('id_fixed_schedule', $activeSchedule->id)
        ->where('day_of_week', $dayOfWeek)
        ->whereIn('id_session', $includedSessions)
        ->exists();

    if ($conflictFixed) {
        return response()->json([
            'status' => 'error',
            'message' => 'Sesi telah digunakan oleh jadwal tetap',
        ], 422);
    }

    // cek konflik booking lain
    $conflictBooking = Booking::where('id_ruang', $request->id_ruang)
        ->where('date', $request->date)
        ->where('status', 'approve')
        ->where(function ($query) use ($includedSessions) {
            foreach ($includedSessions as $sessionId) {
                $query->orWhere(function ($q) use ($sessionId) {
                    $q->where('start_session', '<=', $sessionId)
                      ->where('end_session', '>=', $sessionId);
                });
            }
        })->exists();

    if ($conflictBooking) {
        return response()->json([
            'status' => 'error',
            'message' => 'Sesi telah dipinjam pada waktu tersebut',
        ], 422);
    }

    $filePath = null;
    if ($request->hasFile('files')) {
        $file = $request->file('files');

        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = $uuid . '.' . $extension;

        $path = $file->storeAs('public/booking', $filename);

        $filePath = $filename;
    }

    $booking = Booking::create([
        'id_student' => auth()->id(),
        'id_ruang' => $request->id_ruang,
        'date' => $request->date,
        'start_session' => $request->start_session,
        'end_session' => $request->end_session,
        'keterangan' => $request->keterangan,
        'status' => 'pending',
        'files' => $filePath,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Booking berhasil dibuat',
        'data' => $booking,
        'included_sessions' => $includedSessions,
    ], 201);
    }

    public function getRequestBooking(Request $request) {
        $query = Booking::query()
            ->leftJoin('ruangs', 'bookings.id_ruang', '=', 'ruangs.id_ruang')
            ->leftJoin('sessions', 'bookings.start_session', '=', 'sessions.id')
            ->select(
                'bookings.*',
                'ruangs.nama_ruang',
                'sessions.name_session'
            )
            ->where('bookings.status', 'pending');

        if($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('ruangs.nama_ruang', 'like', "%{$searchTerm}%")
                ->orWhere('sessions.name_session', 'like', "%{$searchTerm}%");
            });
        }
        $query->orderBy('bookings.created_at', 'desc');
        $bookings = $query->get();
        
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginatedData = PaginationHelper::paginate($bookings, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Booking',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function getRequestBookingById($id) {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data Booking',
            'data' => $booking,
        ], 200);
    }

    public function approveBooking(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        $booking->status = $request->status;
        $booking->approved_by = auth()->user()->name;
        $booking->approved_at = Carbon::now();
        $booking->notes = $request->notes;
        $booking->save();

        if ($booking->status === 'approved') {
            Booking::where('id', '!=', $booking->id)
                ->where('id_ruang', $booking->id_ruang)
                ->where('date', $booking->date)
                ->where('status', 'pending')
                ->where(function ($query) use ($booking) {
                    $query->where(function ($q) use ($booking) {
                        // Booking lain yang start_session <= end_session kita dan end_session >= start_session kita
                        $q->where('start_session', '<=', $booking->end_session)
                        ->where('end_session', '>=', $booking->start_session);
                    });
                })
                ->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->user()->name,
                    'approved_at' => Carbon::now(),
                    'notes' => 'Pesan ini dikirim secara otomatis karena sesi booking beririsan atau sudah dipinjam dengan booking lain yang sudah disetujui.',
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil diperbarui',
            'data' => $booking,
        ], 200);
    }

}