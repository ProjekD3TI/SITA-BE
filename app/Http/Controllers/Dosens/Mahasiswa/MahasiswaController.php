<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MahasiswaController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Mahasiswa Dashboard'
        ]);
    }

    public function getProfileMahasiswa(Request $request)
    {
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

    public function editProfileMahasiswa(Request $request)
    {
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
