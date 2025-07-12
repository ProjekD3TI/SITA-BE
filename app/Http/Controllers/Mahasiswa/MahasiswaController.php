<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\DetailPeminjamanInventaris;
use App\Models\AdminPengelola\PeminjamanBhp;
use App\Models\AdminPengelola\PeminjamanInventaris;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MahasiswaController extends Controller
{
    public function dashboard() {
        //menampilkan data peminjaman bhp dan inventaris user yang login dengan status peminjaman yang belum dikembalikan
         $peminjaman_bhp = PeminjamanBhp::where(function($q) {
            $q->where('id_peminjam', Auth::id())
              ->where('tipe_peminjam', 'mahasiswa')
              ->orWhere(function($subQ) {
                  $subQ->where('id_peminjam', Auth::guard('dosen')->id())
                       ->where('tipe_peminjam', 'dosen');
              });
        })
        ->where('status_pengembalian', '!=', 'selesai')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $peminjaman_inventaris = PeminjamanInventaris::where(function($q) {
            $q->where('id_peminjam', Auth::id())
              ->where('tipe_peminjam', 'mahasiswa')
              ->orWhere(function($subQ) {
                  $subQ->where('id_peminjam', Auth::guard('dosen')->id())
                       ->where('tipe_peminjam', 'dosen');
              });
        })
        ->where('status_peminjaman', '!=', 'selesai') 
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Mahasiswa',
            'data' => [
                'peminjaman_bhp' => $peminjaman_bhp,
                'peminjaman_inventaris' => $peminjaman_inventaris,
            ],
        ]);
    }

    public function showPeminjamanBhpUser(Request $request) {
        $query = PeminjamanBhp::query();
        
        $query->where(function($q) {
            $q->where('id_peminjam', Auth::id())
            ->where('tipe_peminjam', 'mahasiswa')
            ->orWhere(function($subQ) {
                $subQ->where('id_peminjam', Auth::guard('dosen')->id())
                    ->where('tipe_peminjam', 'dosen');
            });
        });
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_peminjam', 'like', "%{$searchTerm}%")
                ->orWhere('ni_peminjam', 'like', "%{$searchTerm}%")
                ->orWhere('kegunaan', 'like', "%{$searchTerm}%");
            });
        }
        
        if ($request->has('status_pengembalian') && $request->status_pengembalian !== '') {
            $query->where('status_pengembalian', $request->status_pengembalian);
        }
        
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->whereDate('tanggal_pinjam', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date !== '') {
            $query->whereDate('tanggal_pinjam', '<=', $request->end_date);
        }
        
        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'no' => 'id_peminjaman_bhp',
            'nama_peminjam' => 'nama_peminjam',
            'tanggal_pinjam_formatted' => 'tanggal_pinjam',
            'jumlah_penggunaan' => 'jumlah_penggunaan',
            'status' => 'status_pengembalian',
            'created_at' => 'created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
        
        $peminjaman_bhp = $query->get();
            
        
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($peminjaman_bhp, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data Peminjaman Bhp',
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

   public function detailPeminjamanBhp($id, Request $request)  {
        $peminjaman_bhp = PeminjamanBhp::where('id_peminjaman_bhp', $id)
            ->where(function($query) {
                $query->where('id_peminjam', Auth::id())
                    ->where('tipe_peminjam', 'mahasiswa');
            })
            ->orWhere(function($query) {
                $query->where('id_peminjam', Auth::guard('dosen')->id())
                    ->where('tipe_peminjam', 'dosen');
            })
            ->first();
        
        if (!$peminjaman_bhp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Peminjaman BHP tidak ditemukan atau Anda tidak memiliki akses'
            ], 404);
        }

        if ($request->has('export') && $request->export =='pdf') {
                $bhp = Bhp::find($peminjaman_bhp->id_bhp);
                if (!$bhp) {
                    return response()->json(['message' => 'BHP tidak ditemukan'], 404);
                }
                
                $data = [
                    'peminjaman' => $peminjaman_bhp,
                    'bhp' => $bhp,
                    'tanggalPinjam' => \Carbon\Carbon::parse($peminjaman_bhp->tanggal_pinjam)->format('d/m/Y'),
                ];
                $pdf = Pdf::loadView('peminjaman.detail_bhp', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true);

                return $pdf->download('peminjaman_bhp_' . $peminjaman_bhp->id_peminjaman_bhp . '.pdf');
            } 

        return response()->json([
            'status' => 'success',
            'message' => 'Detail Peminjaman BHP',
            'data' => $peminjaman_bhp
        ], 200);
    }

    public function showPeminjamanInventarisUser(Request $request) {
        $query = PeminjamanInventaris::with(['detailPeminjamanInventaris']);
        
        $query->where(function($q) {
            $q->where('id_peminjam', Auth::id())
            ->where('tipe_peminjam', 'mahasiswa')
            ->orWhere(function($subQ) {
                $subQ->where('id_peminjam', Auth::guard('dosen')->id())
                    ->where('tipe_peminjam', 'dosen');
            });
        });

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('referensi', 'like', "%{$searchTerm}%")
                ->orWhere('nama_peminjam', 'like', "%{$searchTerm}%")
                ->orWhere('ni_peminjam', 'like', "%{$searchTerm}%")
                ->orWhere('kegunaan', 'like', "%{$searchTerm}%")
                ->orWhere('no_telp', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('start_date') && $request->start_date !== '') {
            $query->whereDate('tanggal_pinjam', '>=', $request->start_date);
        }
            
        if ($request->has('end_date') && $request->end_date !== '') {
            $query->whereDate('tanggal_pinjam', '<=', $request->end_date);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortType = $request->input('sort_type', 'desc');

        $sortableColumns = [
            'referensi' => 'referensi',
            'nama_peminjam' => 'nama_peminjam',
            'ni_peminjam' => 'ni_peminjam',
            'tanggal_pinjam' => 'tanggal_pinjam',
            'tipe_peminjam' => 'tipe_peminjam',
            'kegunaan' => 'kegunaan',
            'created_at' => 'created_at',
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $sortColumn = $sortableColumns[$sortBy];
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';
        $query->orderBy($sortColumn, $sortDirection);        
        $peminjaman_inventaris = $query->get();
        
        $peminjaman_inventaris = $peminjaman_inventaris->map(function($item) {
            $totalDipinjam = $item->detailPeminjamanInventaris->count();
            
            $totalDikembalikan = $item->detailPeminjamanInventaris->whereNotNull('tanggal_kembali')->count();
            
            $returnStatus = 'Dipinjam';
            if ($totalDikembalikan > 0) {
                if ($totalDikembalikan >= $totalDipinjam) {
                    $returnStatus = 'Selesai';
                } else {
                    $returnStatus = 'Dikembalikan Sebagian';
                }
            }
            
            $item->total_peminjaman = $totalDipinjam;
            $item->total_dikembalikan = $totalDikembalikan;
            $item->return_status = $returnStatus;
            $item->status_peminjaman = $item->status_peminjaman === 'selesai' ? 'Selesai' : 'Dipinjam';
            $item->tanggal_pinjam = Carbon::parse($item->tanggal_pinjam)->format('Y-m-d');
            $item->created_at = Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
            
            return $item;
        });

        if ($request->has('status') && !empty($request->status)) {
            $statusFilter = $request->status;
            $peminjaman_inventaris = $peminjaman_inventaris->filter(function($item) use ($statusFilter) {
                return $item->return_status === $statusFilter;
            });
        }

        $totalPeminjaman = $peminjaman_inventaris->count();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedData = PaginationHelper::paginate($peminjaman_inventaris, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data peminjaman inventaris',
            'data' => $paginatedData->items(),
            'total_peminjaman' => $totalPeminjaman,
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

    public function detailPeminjamanInventaris($id_peminjaman_inventaris, Request $request)  {
        $peminjaman = PeminjamanInventaris::with('detailPeminjamanInventaris.inventaris.jenisInventaris')
            ->where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
            ->where(function($query) {
                $query->where('id_peminjam', Auth::id())
                    ->where('tipe_peminjam', 'mahasiswa');
            })
            ->orWhere(function($query) {
                $query->where('id_peminjam', Auth::guard('dosen')->id())
                    ->where('tipe_peminjam', 'dosen');
            })
            ->first();
            
        if (!$peminjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Peminjaman tidak ditemukan atau Anda tidak memiliki akses',
            ], 404);
        }

        if ($request->has('export') && $request->export == 'pdf') {
            if (!$peminjaman->detailPeminjamanInventaris || $peminjaman->detailPeminjamanInventaris->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Detail peminjaman tidak ditemukan',
                ], 404);
            }
            
            $data = [
                'peminjaman' => $peminjaman,
                'detail' => $peminjaman->detailPeminjamanInventaris,
                'tanggalPinjam' => \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d/m/Y'),
            ];
            

            $pdf = Pdf::loadView('peminjaman.detail_inventaris', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true);

            return $pdf->download("peminjaman_{$peminjaman->referensi}.pdf");
        }
        
        $detail = DetailPeminjamanInventaris::with('inventaris.jenisInventaris')
            ->where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'message' => 'Detail peminjaman berhasil diambil',
            'data' => [
                'peminjaman' => $peminjaman,
                'detail' => $detail
            ]
        ]);
    }

    public function getProfileMahasiswa(Request $request) {
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

    public function editProfileMahasiswa(Request $request) {
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
