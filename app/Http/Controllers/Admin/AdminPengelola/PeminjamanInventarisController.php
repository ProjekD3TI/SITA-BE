<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\JenisInventaris;
use App\Models\AdminPengelola\PeminjamanInventaris;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PeminjamanInventarisController extends Controller
{

    public function getPeminjamanGrouped(Request $request) {
        $query = PeminjamanInventaris::select(
            'referensi',
            DB::raw('COUNT(DISTINCT id_peminjaman_inventaris) as total_items'),
            DB::raw('MAX(tanggal_pinjam) as tanggal_pinjam'),
            DB::raw('MIN(created_at) as created_at'),
            'nama_peminjam',
            'tipe_peminjam',
            DB::raw('SUM(CASE WHEN status_peminjaman = 1 THEN 1 ELSE 0 END) as returned_count')
        )
        ->groupBy('referensi', 'nama_peminjam', 'tipe_peminjam');
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('referensi', 'like', "%{$searchTerm}%")
                  ->orWhere('nama_peminjam', 'like', "%{$searchTerm}%");
            });
        }
        
        $query->orderBy('created_at', 'desc');
        
        // cek apabila ada peminjaman yang udah dikembalikan
        $peminjamanInventaris = $query->get();
    
        $peminjamanInventaris = $peminjamanInventaris->map(function($item) {
            if ($item->returned_count == 0) {
                $item->return_status = 'Belum Dikembalikan';
            } elseif ($item->returned_count < $item->total_items) {
                $item->return_status = 'Sebagian Dikembalikan';
            } else {
                $item->return_status = 'Sudah Dikembalikan';
            }
            
            unset($item->returned_count);
            return $item;
        });
        
        if ($request->has('status') && !empty($request->status)) {
            $peminjamanInventaris = $peminjamanInventaris->filter(function($item) use ($request) {
                return $item->return_status === $request->status;
            })->values();
        }
        
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        
        $paginatedData = PaginationHelper::paginate($peminjamanInventaris, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data peminjaman Inventaris',
            'data' => $paginatedData->items(),
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
            ]
        ], 200);
    }

    public function createPeminjamanInventaris(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_inventaris' => 'required|array',
            'id_inventaris.*' => 'required|exists:inventaris,id_inventaris',
            'id_peminjam' => 'required',
            'tipe_peminjam' => 'required|in:mahasiswa,dosen',
            'kegunaan' => 'required',
            'tanggal_pinjam' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error Validation',
                'errors' => $validator->errors()
            ], 400);
        }
    
        $user = null;
        $nomor_identitas = null;
    
        if ($request->tipe_peminjam === 'mahasiswa') {
            $user = User::find($request->id_peminjam);
            if ($user) {
                $nomor_identitas = $user->nim;
            }
        } else if ($request->tipe_peminjam === 'dosen') {
            $user = Dosen::find($request->id_peminjam);
            if ($user) {
                $nomor_identitas = $user->nik;
            }
        }
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    
        $tanggal_pinjam = date('Y-m-d', strtotime($request->tanggal_pinjam));
        $sekarang = date('Y-m-d');
        if ($tanggal_pinjam > $sekarang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal pinjam tidak boleh lebih dari hari ini',
            ], 400);
        }
    
        $inv = $request->id_inventaris;
        
        // cek klo bukan array dibuat array
        if (!is_array($inv)) {
            $inv = [$inv];
        }
    
        $kondisi_rusak = [];
        foreach ($inv as $inventaris_id) {
            $inventaris = Inventaris::find($inventaris_id);
            
            if (!$inventaris) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Inventaris tidak ditemukan",
                ], 404);
            }
            
            if ($inventaris->kondisi != 'baik') {
                $kondisi_rusak[] = [
                    'id' => $inventaris_id,
                    'nama' => $inventaris->nama_inventaris ?? 'Inventaris #' . $inventaris_id,
                    'kondisi' => $inventaris->kondisi
                ];
            }

            if ($inventaris->status == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Inventaris ini sedang dipinjam",
                ], 400);
            }
        }
    
        if (count($kondisi_rusak) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Beberapa inventaris dalam kondisi rusak atau hilang',
                'data' => $kondisi_rusak
            ], 400);
        }
    
        // buat no ref
        $no_referensi = 'INV-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        $peminjaman_items = [];
        try {
            foreach ($inv as $inventaris_id) {
                $peminjaman_item = PeminjamanInventaris::create([
                    'id_inventaris' => $inventaris_id,
                    'id_peminjam' => $request->id_peminjam,
                    'nama_peminjam' => $user->name,
                    'tipe_peminjam' => $request->tipe_peminjam,
                    'ni_peminjam' => $nomor_identitas,
                    'no_telp' => $user->no_telp,
                    'kegunaan' => $request->kegunaan,
                    'tanggal_pinjam' => $request->tanggal_pinjam,
                    'status_peminjaman' => 0,
                    'kondisi_akhir_pengembalian' => null,
                    'referensi' => $no_referensi, 
                ]);
                
                $inventaris = Inventaris::find($inventaris_id);
                $inventaris->status = 0;
                $inventaris->save();

                $peminjaman_items[] = $peminjaman_item;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat peminjaman inventaris',
                'error' => $e->getMessage()
            ], 500);
        }
    
        $item_count = count($inv);
    
        try {
            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman inventaris $item_count item oleh " . $user->name . " pada tanggal " . $request->tanggal_pinjam . ". Peminjaman dibuat oleh " . auth()->user()->name);
        } catch (\Exception $e) {
            \Log::warning('Failed to send Telegram notification: ' . $e->getMessage());
        }
    
        return response()->json([
            'status' => 'success',
            'message' => "Peminjaman $item_count Inventaris berhasil dibuat",
            'data' => $peminjaman_items,
        ], 201);
    }

    public function getPeminjamanByReference($referensi) {
        $peminjaman = PeminjamanInventaris::where('referensi', $referensi)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($peminjaman->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman tidak ditemukan',
            ], 404);
        }
        
        // fetch detail inventaris yg dipinjam
        foreach ($peminjaman as $pinjam) {
            $inventaris = Inventaris::find($pinjam->id_inventaris);
            if ($inventaris) {
                $jenisInventaris = JenisInventaris::find($inventaris->id_jenis_inventaris);
                $pinjam->inventaris = [
                    'id_inventaris' => $inventaris->id_inventaris,
                    'nama_inventaris' => $jenisInventaris->nama_inventaris,
                    'no_inventaris' => $inventaris->no_inventaris ?? '-',
                    'kondisi' => $inventaris->kondisi
                ];
            } else {
                $pinjam->inventaris = [
                    'id_inventaris' => $pinjam->id_inventaris,
                    'nama_inventaris' => 'Inventaris tidak ditemukan',
                    'no_inventaris' => '-',
                    'kondisi' => '-'
                ];
            }
        }

        $infoUmum = [
            'referensi' => $referensi,
            'nama_peminjam' => $peminjaman[0]->nama_peminjam,
            'tipe_peminjam' => $peminjaman[0]->tipe_peminjam,
            'ni_peminjam' => $peminjaman[0]->ni_peminjam,
            'no_telp' => $peminjaman[0]->no_telp,
            'tanggal_pinjam' => $peminjaman[0]->tanggal_pinjam,
            'kegunaan' => $peminjaman[0]->kegunaan,
            'total_items' => $peminjaman->count()
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Detail peminjaman berhasil diambil',
            'info_umum' => $infoUmum,
            'items' => $peminjaman
        ]);
    }

    public function pengembalianInventaris(Request $request) {
        $validator = Validator::make($request->all(), [
            'referensi' => 'required|exists:peminjaman_inventaris,referensi',
            'tanggal_kembali' => 'required|date',
            'catatan_pengembalian' => 'nullable|string',
            'kondisi_baik' => 'nullable|array',
            'kondisi_rusak' => 'nullable|array',
            'kondisi_hilang' => 'nullable|array',
            'partial_return' => 'nullable|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error Validation',
                'errors' => $validator->errors()
            ], 400);
        }
    
        $referensi = $request->referensi;
        
        // cek apakah no ref ini ada dan belum dikembalikan
        $peminjaman = PeminjamanInventaris::where('referensi', $referensi)
            ->where('status_peminjaman', 0) 
            ->get();
        
        if ($peminjaman->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semua item dengan referensi ini sudah dikembalikan atau referensi tidak ditemukan',
            ], 400);
        }
        
        $kondisi_baik = $request->kondisi_baik ?? [];
        $kondisi_rusak = $request->kondisi_rusak ?? [];
        $kondisi_hilang = $request->kondisi_hilang ?? [];
        
        $all_ids = array_merge($kondisi_baik, $kondisi_rusak, $kondisi_hilang);
        if (empty($all_ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada inventaris yang dipilih untuk dikembalikan',
            ], 400);
        }
        
        $valid_ids = $peminjaman->pluck('id_inventaris')->toArray();
        
        // cek apakah ini bagian dari peminjaman
        $invalid_ids = array_diff($all_ids, $valid_ids);
        if (!empty($invalid_ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Beberapa inventaris yang dimasukkan tidak tercatat dalam peminjaman ini',
                'invalid_ids' => $invalid_ids
            ], 400);
        }
        
        // cek item yang duplikat
        $duplicate_check = [];
        $has_duplicates = false;
        
        foreach ($all_ids as $id) {
            if (isset($duplicate_check[$id])) {
                $has_duplicates = true;
                break;
            }
            $duplicate_check[$id] = true;
        }
        
        if ($has_duplicates) {
            return response()->json([
                'status' => 'error',
                'message' => 'Setiap inventaris hanya boleh dipilih dalam satu kategori kondisi',
            ], 400);
        }
        
        if (empty($request->partial_return) || $request->partial_return === false) {
            $missing_ids = array_diff($valid_ids, $all_ids);
            if (!empty($missing_ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak semua inventaris yang dipinjam dimasukkan dalam pengembalian',
                    'missing_ids' => $missing_ids
                ], 400);
            }
        }
    
        $updated_items = [];
        DB::beginTransaction();
        
        //proses pengembalian berdasakan kodnisi
        try {
            
            foreach ($kondisi_baik as $id_inventaris) {
                $this->processPengembalian($id_inventaris, $referensi, $request->tanggal_kembali, $request->catatan_pengembalian, 'baik');
                $updated_items[] = $id_inventaris;
            }
            
            foreach ($kondisi_rusak as $id_inventaris) {
                $this->processPengembalian($id_inventaris, $referensi, $request->tanggal_kembali, $request->catatan_pengembalian, 'rusak');
                $updated_items[] = $id_inventaris;
            }
            
            foreach ($kondisi_hilang as $id_inventaris) {
                $this->processPengembalian($id_inventaris, $referensi, $request->tanggal_kembali, $request->catatan_pengembalian, 'hilang');
                $updated_items[] = $id_inventaris;
            }
            
            DB::commit();
            
            try {
                $item_count = count($updated_items);
                $user_name = auth()->user()->name ?? 'System';
                
                // cek apakah yang dikembalikan parsial atau full
                $remaining_items = PeminjamanInventaris::where('referensi', $referensi)
                    ->where('status_peminjaman', 0)
                    ->count();
                
                $total_items = PeminjamanInventaris::where('referensi', $referensi)->count();
                    
                if ($remaining_items > 0) {
                    $message = "Pengembalian parsial: $item_count dari $total_items item dengan referensi $referensi telah dikembalikan oleh $user_name pada tanggal {$request->tanggal_kembali}. Tersisa $remaining_items item yang belum dikembalikan.";
                } else {
                    $message = "Pengembalian lengkap: Seluruh $total_items item dengan referensi $referensi telah dikembalikan oleh $user_name pada tanggal {$request->tanggal_kembali}.";
                }

                $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $message);
            } catch (\Exception $e) {
                \Log::warning('Failed to send Telegram notification: ' . $e->getMessage());
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Pengembalian inventaris berhasil diproses',
                'updated_items' => $updated_items,
                'remaining_items' => $remaining_items ?? 0
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing pengembalian: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pengembalian inventaris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // helper 
    private function processPengembalian($id_inventaris, $referensi, $tanggal_kembali, $catatan, $kondisi) {
        PeminjamanInventaris::where('id_inventaris', $id_inventaris)
            ->where('referensi', $referensi)
            ->where('status_peminjaman', 0) 
            ->update([
                'status_peminjaman' => 1,
                'tanggal_kembali' => $tanggal_kembali,
                'catatan_pengembalian' => $catatan,
                'kondisi_akhir_pengembalian' => $kondisi
            ]);
        
        $inventaris = Inventaris::find($id_inventaris);
        if ($inventaris) {
            $inventaris->status = 1; 
            if ($kondisi === 'rusak' && $inventaris->kondisi === 'baik') {
                $inventaris->kondisi = 'rusak';
            } else if ($kondisi === 'hilang') {
                $inventaris->kondisi = 'hilang';
            }
            $inventaris->save();
        }
    }


    private function sendTelegramMessage($chatId, $message) {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage";
        Http::post($url, [
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message
        ]);
    }
}
