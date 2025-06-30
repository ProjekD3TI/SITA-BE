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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function getUserBooking(Request $request) {
        $query = Booking::query()
            ->leftJoin('ruangs', 'bookings.id_ruang', '=', 'ruangs.id_ruang')
            ->leftJoin('sessions', 'bookings.start_session', '=', 'sessions.id')
            ->select('bookings.*', 'ruangs.nama_ruang', 'sessions.name_session', 'sessions.start_time', 'sessions.end_time')
            ->where(function ($q) {
                $user = auth()->user();
                if ($user->is_mahasiswa) {
                    $q->where('id_student', $user->id);
                }
                if ($user->is_dosen) {
                    $q->orWhere('id_dosen', $user->id);
                }
            });
            

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
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
            return response()->json(['status' => 'error', 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $startSession = Session::find($request->start_session);
        $endSession = Session::find($request->end_session);

        if (!$startSession || !$endSession || strtotime($startSession->start_time) >= strtotime($endSession->start_time)) {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak valid atau tidak berurutan'], 422);
        }

        $intermediateSessions = Session::whereBetween('id', [$request->start_session, $request->end_session])->orderBy('start_time')->get();
        if (count($intermediateSessions) != ($request->end_session - $request->start_session + 1)) {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak lengkap atau tidak tersedia'], 422);
        }

        for ($i = 0; $i < count($intermediateSessions) - 1; $i++) {
            if (strtotime($intermediateSessions[$i]->end_time) > strtotime($intermediateSessions[$i + 1]->start_time)) {
                return response()->json(['status' => 'error', 'message' => 'Waktu sesi tidak berurutan'], 422);
            }
        }

        $includedSessions = Session::where(function ($query) use ($startSession, $endSession) {
            $query->whereBetween('start_time', [$startSession->start_time, $endSession->end_time])
                  ->orWhereBetween('end_time', [$startSession->start_time, $endSession->end_time])
                  ->orWhere(function ($q) use ($startSession, $endSession) {
                      $q->where('start_time', '<=', $startSession->start_time)
                        ->where('end_time', '>=', $endSession->end_time);
                  });
        })->pluck('id');

        $dayOfWeek = date('N', strtotime($request->date));
        $activeSchedule = FixedSchedule::where('id_ruang', $request->id_ruang)
            ->where('start_date', '<=', $request->date)
            ->where('end_date', '>=', $request->date)->first();

        if (!$activeSchedule) {
            return response()->json(['status' => 'error', 'message' => 'Diluar rentang semester'], 422);
        }

        $conflictFixed = FixedScheduleSession::where('id_fixed_schedule', $activeSchedule->id)
            ->where('day_of_week', $dayOfWeek)
            ->whereIn('id_session', $includedSessions)->exists();

        if ($conflictFixed) {
            return response()->json(['status' => 'error', 'message' => 'Bertabrakan dengan jadwal tetap'], 422);
        }

        $conflictBooking = Booking::where('id_ruang', $request->id_ruang)
            ->where('date', $request->date)
            ->where('status', 'approve')
            ->where(function ($query) use ($includedSessions) {
                foreach ($includedSessions as $sessionId) {
                    $query->orWhere(function ($q) use ($sessionId) {
                        $q->where('start_session', '<=', $sessionId)->where('end_session', '>=', $sessionId);
                    });
                }
            })->exists();

        if ($conflictBooking) {
            return response()->json(['status' => 'error', 'message' => 'Sesi telah dipinjam'], 422);
        }

        $filePath = null;
        if ($request->hasFile('files')) {
            $file = $request->file('files');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/booking', $filename);
            $filePath = $filename;
        }

        $user = auth()->user();
        $booking = Booking::create([
            'id_student' => $user->is_mahasiswa ? $user->id : null,
            'id_dosen' => $user->is_dosen ? $user->id : null,
            'id_ruang' => $request->id_ruang,
            'date' => $request->date,
            'start_session' => $request->start_session,
            'end_session' => $request->end_session,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
            'files' => $filePath,
        ]);

        $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Booking ruang oleh {$user->name} untuk {$request->date}");

        return response()->json(['status' => 'success', 'message' => 'Booking dibuat', 'data' => $booking], 201);
    }

    public function approveBooking(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $booking = Booking::find($id);
        if (!$booking) return response()->json(['status' => 'error', 'message' => 'Booking tidak ditemukan'], 404);

        $booking->update([
            'status' => $request->status,
            'approved_by' => auth()->user()->name,
            'approved_at' => Carbon::now(),
            'notes' => $request->notes
        ]);

        if ($booking->user && $booking->user->email) {
            $status = $request->status;
            $emailView = $status === 'approved' ? 'emails.ruang.booking_approved' : 'emails.ruang.booking_rejected';
            $subject = $status === 'approved' ? 'Peminjaman Disetujui' : 'Peminjaman Ditolak';

            Mail::send($emailView, ['booking' => $booking], function ($message) use ($booking, $subject) {
                $message->to($booking->user->email)->subject($subject);
            });
        }

        if ($request->status === 'approved') {
            $conflicts = Booking::where('id', '!=', $booking->id)
                ->where('id_ruang', $booking->id_ruang)
                ->where('date', $booking->date)
                ->where('status', 'pending')
                ->where(function ($query) use ($booking) {
                    $query->where('start_session', '<=', $booking->end_session)
                          ->where('end_session', '>=', $booking->start_session);
                })->get();

            foreach ($conflicts as $conflict) {
                $conflict->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->user()->name,
                    'approved_at' => now(),
                    'notes' => 'Ditolak otomatis karena bertabrakan dengan booking yang disetujui.',
                ]);

                if ($conflict->user && $conflict->user->email) {
                    Mail::send('emails.ruang.booking_rejected', ['booking' => $conflict], function ($message) use ($conflict) {
                        $message->to($conflict->user->email)->subject('Peminjaman Ditolak');
                    });
                }
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Booking diperbarui', 'data' => $booking], 200);
    }

    private function sendTelegramMessage($chatId, $message)
    {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage";
    
        $response = Http::withOptions([
            'verify' => false,
        ])->post($url, [
            'chat_id' => $chatId,
            'text' => $message
        ]);
    
        // \Log::info('Telegram request:', ['chat_id' => $chatId, 'message' => $message]);
        // \Log::info('Telegram response:', $response->json());
    }
    

    public function getRequestBooking(Request $request)
    {
        $query = Booking::query()
            ->leftJoin('ruangs', 'bookings.id_ruang', '=', 'ruangs.id_ruang')
            ->leftJoin('sessions as start_sess', 'bookings.start_session', '=', 'start_sess.id')
            ->leftJoin('sessions as end_sess', 'bookings.end_session', '=', 'end_sess.id')
            ->select(
                'bookings.*',
                'ruangs.nama_ruang',
                'start_sess.name_session as start_session_name',
                'start_sess.start_time as start_time',
                'end_sess.name_session as end_session_name',
                'end_sess.end_time as end_time'
            )
            ->orderBy('bookings.created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('ruangs.nama_ruang', 'like', "%{$searchTerm}%")
                  ->orWhere('bookings.keterangan', 'like', "%{$searchTerm}%")
                  ->orWhere('bookings.status', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $bookings = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Booking Permintaan',
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ]
        ]);
    }
    public function getAvailableSessions(Request $request)
{
    $request->validate([
        'id_ruang' => 'required|exists:ruangs,id_ruang',
        'date' => 'required|date',
    ]);

    $dayOfWeek = date('N', strtotime($request->date));

    // Ambil semua sesi
    $allSessions = Session::orderBy('start_time')->get();

    // Cek jadwal tetap
    $fixedSchedule = FixedSchedule::where('id_ruang', $request->id_ruang)
        ->where('start_date', '<=', $request->date)
        ->where('end_date', '>=', $request->date)
        ->first();

    $blockedFixed = [];
    if ($fixedSchedule) {
        $blockedFixed = FixedScheduleSession::where('id_fixed_schedule', $fixedSchedule->id)
            ->where('day_of_week', $dayOfWeek)
            ->pluck('id_session')
            ->toArray();
    }

    // Cek booking approved
    $bookings = Booking::where('id_ruang', $request->id_ruang)
        ->where('date', $request->date)
        ->where('status', 'approved')
        ->get();

    $blockedBooking = [];
    foreach ($bookings as $booking) {
        for ($i = $booking->start_session; $i <= $booking->end_session; $i++) {
            $blockedBooking[] = $i;
        }
    }

    $blockedSessions = array_unique(array_merge($blockedFixed, $blockedBooking));

    $availableSessions = $allSessions->filter(function ($session) use ($blockedSessions) {
        return !in_array($session->id, $blockedSessions);
    })->values();

    return response()->json([
        'status' => 'success',
        'data' => $availableSessions
    ]);
}

}