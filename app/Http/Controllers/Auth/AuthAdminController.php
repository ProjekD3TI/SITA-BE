<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthAdminController extends Controller
{
    public function registerAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admin,email',
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'role' => 'required',
        ]
        , [
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal harus 8 karakter',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, dan 1 angka',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin created',
            'data' => $admin
        ], 201);
    }

    public function showAdmin(Request $request) {
        $query = Admin::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');
        
        $sortableColumns = [
            'no' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'created_at' => 'created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        $admins = $query->get();
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($admins, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Admin',
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

    public function findAdmin($id) {
        $admin = Admin::find($id);
        if ($admin) {
            return response()->json($admin);
        } else {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }
    }

    public function editAdmin(Request $request, $id) {
        $db_admin = Admin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admin,email,' . $id,
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        $db_admin->update($data);

        return response()->json(['message' => 'Admin berhasil diupdate'], 200);
    }

    public function deleteAdmin($id) {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin berhasil dihapus'], 200);
    }

    public function loginAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 400);
        }

        $role = $admin->role;

            // generate token tergantung role 
            $token = $admin->createToken('auth_token', ['role:' . $role])->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Login',
                'data' => [
                    'admin' => $admin,
                    'token' => $token,
                    'role' => $role
                ]
            ]);
        }

    public function getProfileAdmin(Request $request) {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terautentikasi',
            ], 401);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Data profile admin',
            'data' => $admin,
        ]);
    }

    public function updateProfileAdmin(Request $request) {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terautentikasi',
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('admin')->ignore($admin->id),
            ],
            'password' => [
                'nullable',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'password_confirmation' => 'nullable|same:password',
        ], [
            'email.unique' => 'Email sudah digunakan oleh admin lain',
            'password.regex' => 'Password harus mengandung huruf kecil, huruf besar, dan angka',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi error',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        if ($request->filled('name')) {
            $admin->name = $request->name;
        }
        
        if ($request->filled('email')) {
            $admin->email = $request->email;
        }
        
        if ($request->filled('password')) {
            $admin->password = bcrypt($request->password);
        }
        
        $admin->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Profile admin berhasil diperbarui',
            'data' => $admin,
        ]);
    }

}
    

