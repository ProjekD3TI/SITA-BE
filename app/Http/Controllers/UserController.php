<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        return $request->user();
    }

    public function logoutUser(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function sendResetLinkMahasiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $mahasiswa = User::where('email', $request->email)->first();
            if (!$mahasiswa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email mahasiswa tidak ditemukan'
                ], 404);
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            $resetLink = url("/mahasiswa/reset-password/{$token}");

            Mail::send('emails.mahasiswa.reset-password', ['resetLink' => $resetLink], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Reset Password Mahasiswa');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Link reset password telah dikirim ke email Anda'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim link reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPasswordMahasiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $resetRecord = DB::table('password_reset_tokens')
                ->get()
                ->filter(function ($record) use ($request) {
                    return Hash::check($request->token, $record->token);
                })
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token tidak valid'
                ], 400);
            }

            $mahasiswa = User::where('email', $resetRecord->email)->first();
            if (!$mahasiswa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mahasiswa tidak ditemukan'
                ], 404);
            }

            $mahasiswa->password = bcrypt($request->password);
            $mahasiswa->save();

            DB::table('password_reset_tokens')
                ->where('token', Hash::make($request->token))
                ->delete();

            Mail::send('emails.mahasiswa.password', [
                'password' => $request->password
            ], function ($message) use ($resetRecord) {
                $message->to($resetRecord->email)
                    ->subject('Password Berhasil Diubah');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diubah'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendResetLinkDosen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $dosen = Dosen::where('email', $request->email)->first();
            if (!$dosen) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email dosen tidak ditemukan'
                ], 404);
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            $resetLink = url("/dosen/reset-password/{$token}");

            Mail::send('emails.dosen.reset-password', ['resetLink' => $resetLink], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Reset Password Dosen');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Link reset password telah dikirim ke email Anda'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim link reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPasswordDosen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $resetRecord = DB::table('password_reset_tokens')
                ->get()
                ->filter(function ($record) use ($request) {
                    return Hash::check($request->token, $record->token);
                })
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token tidak valid'
                ], 400);
            }

            $dosen = Dosen::where('email', $resetRecord->email)->first();
            if (!$dosen) {
                return response()->json([
                    'status' => false,
                    'message' => 'Dosen tidak ditemukan'
                ], 404);
            }

            $dosen->password = bcrypt($request->password);
            $dosen->save();

            DB::table('password_reset_tokens')
                ->where('token', Hash::make($request->token))
                ->delete();

            Mail::send('emails.dosen.password', [
                'password' => $request->password
            ], function ($message) use ($resetRecord) {
                $message->to($resetRecord->email)
                    ->subject('Password Berhasil Diubah');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diubah'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendResetLinkAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $admin = Admin::where('email', $request->email)->first();
            if (!$admin) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email admin tidak ditemukan'
                ], 404);
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            $resetLink = url("/admin/reset-password/{$token}");

            Mail::send('emails.admin.reset-password', ['resetLink' => $resetLink], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Reset Password Admin');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Link reset password telah dikirim ke email Anda'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim link reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPasswordAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $resetRecord = DB::table('password_reset_tokens')
                ->get()
                ->filter(function ($record) use ($request) {
                    return Hash::check($request->token, $record->token);
                })
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token tidak valid'
                ], 400);
            }

            $admin = Admin::where('email', $resetRecord->email)->first();
            if (!$admin) {
                return response()->json([
                    'status' => false,
                    'message' => 'Admin tidak ditemukan'
                ], 404);
            }

            $admin->password = bcrypt($request->password);
            $admin->save();

            DB::table('password_reset_tokens')
                ->where('token', Hash::make($request->token))
                ->delete();

            Mail::send('emails.admin.password', [
                'password' => $request->password
            ], function ($message) use ($resetRecord) {
                $message->to($resetRecord->email)
                    ->subject('Password Berhasil Diubah');
            });

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diubah'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah password: ' . $e->getMessage()
            ], 500);
        }
    }
}
