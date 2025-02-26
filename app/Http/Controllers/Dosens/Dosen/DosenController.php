<?php

namespace App\Http\Controllers\Dosens\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DosenController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Dosen Dashboard',
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
