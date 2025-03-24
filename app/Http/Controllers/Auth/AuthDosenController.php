<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthDosenController extends Controller
{
    public function loginDosen(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 401);
        }
    
        $dosen = Dosen::where('email', $request->email)->first();
    
        if (!$dosen || !Hash::check($request->password, $dosen->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }
    
        if (!$dosen->status) {
            return response()->json([
                'status' => false,
                'message' => 'Akun anda tidak aktif'
            ], 401);
        }
    
        // matiinn ke false dlu baru cari dosen dr tabel jabatan trus set ke true
        JabatanDosen::where('dosen_id', $dosen->id)
            ->update(['is_active' => false]);
    
        $jabatanDosen = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->whereHas('jabatan', function($query) {
                $query->where('name', 'Dosen'); 
            })
            ->first();
    
        if (!$jabatanDosen) {
            return response()->json([
                'status' => false,
                'message' => 'Akun tidak memiliki akses sebagai Dosen'
            ], 401);
        }
    
        $jabatanDosen->update(['is_active' => true]);
        $currentJabatan = $jabatanDosen->jabatan;
    
        $availableJabatan = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->jabatan->id,
                    'name' => $item->jabatan->name,
                    'is_active' => $item->is_active
                ];
            });
    
        $token = $dosen->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'data' => [
                'dosen' => [
                    'id' => $dosen->id,
                    'name' => $dosen->name,
                    'email' => $dosen->email,
                    'nik' => $dosen->nik,
                    'no_telp' => $dosen->no_telp
                ],
                'jabatan_sekarang' => [
                    'id' => $currentJabatan->id,
                    'name' => $currentJabatan->name
                ],
                'punya_jabatan' => $availableJabatan,
                'token' => $token,
            ]
        ], 200);
    }

    public function switchJabatan(Request $request) {
        $validator = Validator::make($request->all(), [
            'jabatan_id' => 'required|exists:jabatan,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $dosen = auth()->user();
        
        $jabatanDosen = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->where('jabatan_id', $request->jabatan_id)
            ->first();

        if (!$jabatanDosen) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses ke jabatan ini'
            ], 403);
        }

        $primaryJabatan = JabatanDosen::where('dosen_id', $dosen->id)
            ->whereHas('jabatan', function($query) {
                $query->where('name', 'Dosen');
            })
            ->exists();

        if (!$primaryJabatan && $jabatanDosen->jabatan->name !== 'Dosen') {
            return response()->json([
                'status' => false,
                'message' => 'Anda harus memiliki jabatan Dosen untuk mengakses jabatan lain'
            ], 403);
        }

        JabatanDosen::where('dosen_id', $dosen->id)
            ->update(['is_active' => false]);

        $jabatanDosen->update(['is_active' => true]);

        $availableJabatan = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->jabatan->id,
                    'name' => $item->jabatan->name,
                    'is_active' => $item->is_active
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Berpindah ke jabatan ' . $jabatanDosen->jabatan->name,
            'data' => [
                'jabatan_sekarang' => [
                    'id' => $jabatanDosen->jabatan->id,
                    'name' => $jabatanDosen->jabatan->name
                ],
                'punya_jabatan' => $availableJabatan
            ]
        ], 200);
    }


    public function getCurrentJabatan() {
        $dosen = auth()->user();
        
        $currentJabatan = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->where('is_active', true)
            ->first();

        if (!$currentJabatan) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada jabatan yang aktif'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'jabatan_sekarang' => [
                    'id' => $currentJabatan->jabatan->id,
                    'name' => $currentJabatan->jabatan->name
                ]
            ]
        ], 200);
    }

    public function getUserJabatan() {
        $dosen = auth()->user();

        $availableJabatan = JabatanDosen::with('jabatan')
            ->where('dosen_id', $dosen->id)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->jabatan->id,
                    'name' => $item->jabatan->name,
                    'is_active' => $item->is_active
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'dosen' => [
                    'id' => $dosen->id,
                    'name' => $dosen->name,
                    'email' => $dosen->email,
                    'nik' => $dosen->nik,
                    'no_telp' => $dosen->no_telp
                ],
                'punya_jabatan' => $availableJabatan
            ]
        ], 200);
    }

    // public function logout()
    // {
    //     auth()->user()->tokens()->delete();
        
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Logout berhasil'
    //     ], 200);
    // }
}
