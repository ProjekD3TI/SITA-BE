<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SuperadminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Superadmin Dashboard',
        ]);
    }

    public function createDosen(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:dosen', //pake staff g yh
            'password' => [
            'required',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            ],
            'nik' => 'required|string', //max 16??
        ],
        [
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal harus 8 karakter',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, dan 1 angka',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // otomatis buat ke tabel jabatan dosen
        DB::beginTransaction();
    
        try {
            $dosen = Dosen::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'nik' => $request->nik,
                'status' => 1,
            ]);

            $jabatanDosen = Jabatan::where('name', 'Dosen')->first();
            
            if (!$jabatanDosen) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Jabatan Dosen tidak ditemukan'
                ], 404);
            }

            JabatanDosen::create([
                'dosen_id' => $dosen->id,
                'jabatan_id' => $jabatanDosen->id,
                'is_active' => true 
            ]);

            Mail::send('emails.dosen.info-akun', [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ], function($message) use ($dosen) {
                $message->to($dosen->email)
                       ->subject('Informasi Akun Dosen');
            });

            DB::commit();

            $dosen->load('jabatans');

            return response()->json([
                'status' => true,
                'message' => 'Dosen berhasil dibuat',
                'data'  => $dosen,

            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat dosen',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function getDosen() {
        $dosen = Dosen::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $dosen
        ], 200);
    }

    public function setStatusDosen(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $dosen = Dosen::find($request->id);
        if (!$dosen) {
            return response()->json([
                'status' => false,
                'message' => 'Dosen tidak ditemukan'
            ], 404);
        }

        $dosen->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status Dosen berhasil diubah',
        ], 200);
    }

    public function createJabatan(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $jabatan = Jabatan::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jabatan created',
            'data' => $jabatan
        ], 201);

    }

    public function insertJabatan(Request $request) {
        $validator = Validator::make($request->all(), [
            'dosen_id' => 'required|integer',
            'jabatan_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $dosen = Dosen::find($request->dosen_id);
        $dosen->jabatans()->attach($request->jabatan_id, ['is_active' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jabatan ditambahkan',
        ], 201);
    }
    public function deleteJabatan(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $jabatanDosen = JabatanDosen::find($request->id);
        if (!$jabatanDosen) {
            return response()->json([
                'status' => false,
                'message' => 'Jabatan Dosen tidak ditemukan'
            ], 404);
        }

        $jabatanDosen->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jabatan Didelete',
        ], 200);
    }

    // public function getKonfirmasiMahasiswa(Request $request) {
    //     $mahasiswa = User::where('konfirmasi', 0)->get();

    //     $perPage = $request->input('per_page', 10);
    //     $page = $request->input('page', 1);

    //     $paginatedData = PaginationHelper::paginate($mahasiswa, $perPage, $page, [
    //         'path' => $request->url(), 
    //         'query' => $request->query(),
    //     ]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Data Mahasiswa yang belum dikonfirmasi',
    //         'data' => $paginatedData->items(),
    //         'pagination' => [
    //             'total' => $paginatedData->total(),
    //             'per_page' => $paginatedData->perPage(),
    //             'current_page' => $paginatedData->currentPage(),
    //             'last_page' => $paginatedData->lastPage(),
    //         ]
    //     ], 200);
    // }
    
    public function getMahasiswa(Request $request) {
        $mahasiswa = User::where('status', 1)->get();

        // set default ke 10 /page dan page 1
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($mahasiswa, $perPage, $page, [
            'path' => $request->url(), 
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Mahasiswa',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    // public function konfirmasiMahasiswa(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer',
    //         'konfirmasi' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()
    //         ], 422);
    //     }

    //     $mahasiswa = User::find($request->id);
    //     if (!$mahasiswa) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Mahasiswa tidak ditemukan'
    //         ], 404);
    //     }

    //     $mahasiswa->update(['konfirmasi' => $request->konfirmasi]);

    //     if ($request->konfirmasi == true) {
    //         Mail::send('emails.mahasiswa.konfirmasi', [], function($message) use ($mahasiswa) {
    //             $message->to($mahasiswa->email)
    //                    ->subject('Akun Berhasil Dikonfirmasi');
    //         });

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Akun Mahasiswa berhasil dikonfirmasi',
    //         ], 200);
    //     } else {
    //         Mail::send('emails.mahasiswa.ditolak', [], function($message) use ($mahasiswa) {
    //             $message->to($mahasiswa->email)
    //                    ->subject('Pendaftaran Akun Ditolak');
    //         });

    //         //biar ga duplikat di delete
    //         $mahasiswa->delete();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Akun mahasiswa ditolak',
    //         ], 200);
    //     }
    // }

    public function setStatusMahasiswa(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $mahasiswa = User::find($request->id);
        if (!$mahasiswa) {
            return response()->json([
                'status' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        $mahasiswa->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status Mahasiswa berhasil diubah',
        ], 200);
    }

}
