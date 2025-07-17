<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\Ruang;
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
            'status' => 'success',
            'message' => 'Dashboard Superadmin',
            'data' => [
                'total_dosen' => Dosen::count(),
                'total_mahasiswa' => User::count(),
                'total_admin' => Admin::count(),
                'total_inventaris' => Inventaris::count(), 
                'total_bhp' => Bhp::count(),
                'total_ruang' => Ruang::count(), 
                'total_gedung' => Gedung::count(),
            ]
        ]);
    }

    public function createDosen(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => ['required', 'email', 'unique:dosen', function ($attribute, $value, $fail) {
                if (!preg_match('/staff.uns.ac.id$/', $value)) {
                    return $fail("Email yang didaftar harus menggunakan format email staff");
                }
            }], 
            'password' => [
            'required',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            ],
            'nik' => 'required|string', //max 16??
            'no_telp' => 'nullable|string|max:16',
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
                'no_telp' => $request->no_telp,
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

    public function getDosen(Request $request) {
        $query = Dosen::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%")
                ->orWhere('nik', 'like', "%{$searchTerm}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'nik' => 'nik',
            'created_at' => 'created_at',
            'status' => 'status'
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        $dosen = $query->get();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($dosen, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Dosen',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_type' => $sortType,
            ]
        ], 200);
    }

    public function getDosenById($id) {
        $dosen = Dosen::find($id);
        if ($dosen) {
            return response()->json($dosen);
        } else {
            return response()->json(['message' => 'Dosen tidak ditemukan'], 404);
        }
    }

    public function editDosen(Request $request, $id) {
        $db_dosen = Dosen::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
            'required',
            'email',
            'unique:dosen,email,' . $id,
            function ($attribute, $value, $fail) {
                if (!preg_match('/staff.uns.ac.id$/', $value)) {
                return $fail("Email yang didaftar harus menggunakan format email staff");
                }
            }
            ],
            'password' => [
            'required',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            ],
            'nik' => 'required|string', //max 16??
            'no_telp' => 'nullable|max:15|string', // Contoh regex untuk nomor telepon internasional
        ],
        [
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal harus 8 karakter',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, dan 1 angka',
            'no_telp.regex' => 'Format nomor telepon tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'no_telp' => $request->no_telp,
        ];

        $db_dosen->update($data);

        return response()->json(['message' => 'Dosen berhasil diupdate'], 200);
    }

    public function setStatusDosen(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|array',
            'id.*' => 'integer|exists:dosen,id',
            'status' => 'required|boolean',
        ], [
            'id.required' => 'Pilih minimal satu dosen',
            'id.array' => 'Format data dosen tidak valid',
            'id.*.exists' => 'Salah satu dosen tidak ditemukan',
            'status.required' => 'Status harus ditentukan',
            'status.boolean' => 'Status harus berupa boolean (true/false)'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $updateCount = Dosen::whereIn('id', $request->id)
            ->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status ' . $updateCount . ' dosen berhasil diubah',
            'data' => [
                'updated_count' => $updateCount,
                'status' => $request->status ? 'aktif' : 'nonaktif'
            ]
        ], 200);
    }

    // public function setStatusDosen(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer',
    //         'status' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()
    //         ], 422);
    //     }

    //     $dosen = Dosen::find($request->id);
    //     if (!$dosen) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Dosen tidak ditemukan'
    //         ], 404);
    //     }

    //     $dosen->update(['status' => $request->status]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Status Dosen berhasil diubah',
    //     ], 200);
    // }

    public function getJabatan() {
        $jabatans = Jabatan::all();
        $jabatan_dosen = JabatanDosen::with('jabatan', 'dosen')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Jabatan',
            'data' => [
                'jabatans' => $jabatans,
                'jabatan_dosen' => $jabatan_dosen
            ],
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
        $query = User::query();
               

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%")
                ->orWhere('nim', 'like', "%{$searchTerm}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'nim' => 'nim',
            'no_telp' => 'no_telp',
            'status' => 'status'
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $mahasiswa = $query->get();

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
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_type' => $sortType,
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
            'id' => 'required|array',
            'id.*' => 'integer|exists:mahasiswa,id',
            'status' => 'required|boolean',
        ], [
            'id.required' => 'Pilih minimal satu mahasiswa',
            'id.array' => 'Format data mahasiswa tidak valid',
            'id.*.exists' => 'Salah satu mahasiswa tidak ditemukan',
            'status.required' => 'Status harus ditentukan',
            'status.boolean' => 'Status harus berupa boolean (true/false)'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $updateCount = User::whereIn('id', $request->id)
            ->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status ' . $updateCount . ' mahasiswa berhasil diubah',
            'data' => [
                'updated_count' => $updateCount,
                'status' => $request->status ? 'aktif' : 'nonaktif'
            ]
        ], 200);
    }

    // public function setStatusMahasiswa(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer',
    //         'status' => 'required|boolean',
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

    //     $mahasiswa->update(['status' => $request->status]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Status Mahasiswa berhasil diubah',
    //     ], 200);
    // }

    public function getMahasiswaById($id) {
        $mahasiswa = User::find($id);
        if ($mahasiswa) {
            return response()->json($mahasiswa);
        } else {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }
    }


}
