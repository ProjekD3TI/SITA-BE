<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

//Controller Auth Mahasiswa
class AuthController extends Controller
{
    
    public function registerUser(Request $request) {
        $user = new User();
        
        $validator = Validator::make($request->all(), [
            'nim' => [
                'required',
                'regex:/^V34[0-9]{5}$/',
                'unique:mahasiswa,nim'
            ],
            'name' => 'required',
            'email' => ['required', 'email', function ($attribute, $value, $fail) {
                if (!preg_match('/student.uns.ac.id$/', $value)) {
                    return $fail("Email yang didaftar harus menggunakan format email student");
                }
            }],
        'password' => [
            'required',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            // 'regex:/[@$!%*#?&]/'
        ],
        'no_telp' => [
            'required',
            'regex:/^([0-9\s\-\+\(\)]*)$/',
            'min:10'
        ],
        ], [
            'nim.regex' => 'Format NIM tidak valid. Silahkan Gunakan NIM sesuai Prodi TI UNS',
            'nim.unique' => 'NIM sudah terdaftar',
            'password.min' => 'Password minimal harus 8 karakter',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, dan 1 angka',
            'no_telp.regex' => 'Format nomor telepon tidak valid.',
            'no_telp.min' => 'Nomor telepon minimal harus 10 digit'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 401);
        }

        $mahasiswa = User::create([
            'nim' => $request->nim,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'no_telp' => $request->no_telp,
            'status' => 1
        ]);

        Mail::send('emails.mahasiswa.info-akun', [
            'name' => $request -> name,
            'nim' => $request -> nim,
            'email' => $request -> email,
            'password' => $request -> password,
        ], function($message) use ($mahasiswa) {
            $message->to($mahasiswa->email)
                   ->subject('Akun Berhasil Dibuat');
        });

        return response()->json([
            'status' => true,
            'message' => 'User created'
        ]);
    }
    public function loginUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 401);
        }

        $mahasiswa = User::where('email', $request->email)->first();

        if (!$mahasiswa || !Hash::check($request->password, $mahasiswa->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        if ($mahasiswa->status != 1) {
            return response()->json([
                'message' => 'Akun anda tidak aktif'
            ], 401);
        }


        $token = $mahasiswa->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $mahasiswa,
                'token' => $token
            ]
        ]);
        
    }


    // public function logoutUser(Request $request) {
    //     $request->user()->currentAccessToken()->delete();
    //     return response()->noContent();
    //     // return response()->json([
    //     //     'status' => true,
    //     //     'message' => 'Berhasil Logout'
    //     // ]);
    // }
}
