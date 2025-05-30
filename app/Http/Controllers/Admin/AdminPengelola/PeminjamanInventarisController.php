<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\DetailPeminjamanInventaris;
use App\Models\AdminPengelola\Inventaris;
use App\Models\AdminPengelola\JenisInventaris;
use App\Models\AdminPengelola\PeminjamanInventaris;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PeminjamanInventarisController extends Controller
{
    
    public function grupPeminjamanInventaris(Request $request) {
        $query = PeminjamanInventaris::with(['detailPeminjamanInventaris']);

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

        $query->orderBy('created_at', 'desc');
        
        $peminjaman_inventaris = $query->get();
        
        $peminjaman_inventaris = $peminjaman_inventaris->map(function($item) {
            $totalDipinjam = $item->detailPeminjamanInventaris->count();
            
            $totalDikembalikan = $item->detailPeminjamanInventaris->whereNotNull('tanggal_kembali')->count();
            
            $returnStatus = 'Belum Dikembalikan';
            if ($totalDikembalikan > 0) {
                if ($totalDikembalikan >= $totalDipinjam) {
                    $returnStatus = 'Sudah Dikembalikan';
                } else {
                    $returnStatus = 'Sebagian Dikembalikan';
                }
            }
            
            $item->total_Peminjaman = $totalDipinjam;
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
            ]
        ], 200);
    }
    
    //baru
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
    
        foreach ($inv as $inventaris_id) {
            $inventaris = Inventaris::find($inventaris_id);
            
            if (!$inventaris) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Inventaris tidak ditemukan",
                    'errors' => $validator->errors()
                ], 400);
            }

            if ($inventaris->status == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Inventaris ini sedang dipinjam",
                ], 400);
            }
        }
        // buat no ref
        $no_referensi = 'INV-' . date('YmdHis') . '-' . rand(1000, 9999);
        $peminjaman_items = [];
        // membuat data di 2 tabel melalui 2 model yaitu peminjaman inventaris dan detail peminjaman inventaris
        try {
            // buat data awal di peminjaman inventaris
            $peminjaman = PeminjamanInventaris::create([
                'id_peminjam' => $request->id_peminjam,
                'nama_peminjam' => $user->name,
                'tipe_peminjam' => $request->tipe_peminjam,
                'ni_peminjam' => $nomor_identitas,
                'no_telp' => $user->no_telp,
                'kegunaan' => $request->kegunaan,
                'tanggal_pinjam' => $request->tanggal_pinjam,
                'status_peminjaman' => 'dipinjam',
                'referensi' => $no_referensi,
                'created_by' => auth()->user()->name,
            ]);
            // buat data di detail peminjaman inventaris dengan mengambil id dari data peminjaman
            foreach ($inv as $inventaris_id) {
                $peminjaman_item = DetailPeminjamanInventaris::create([
                    'id_peminjaman_inventaris' => $peminjaman->id_peminjaman_inventaris,
                    'id_inventaris' => $inventaris_id,
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
            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman inventaris $item_count item oleh " . $user->name . " pada tanggal " . $request->tanggal_pinjam . ". Peminjaman dibuat oleh " . auth()->user()->name . " dengan No Ref: $no_referensi");
        } catch (\Exception $e) {
            \Log::warning('Failed to send Telegram notification: ' . $e->getMessage());
        }
        return response()->json([
            'status' => 'success',
            'message' => "Peminjaman $item_count Inventaris berhasil dibuat",
            'data' => $peminjaman_items,
        ], 201);
    }

    

    public function getDetailPeminjaman($id_peminjaman_inventaris) {
        $peminjaman = PeminjamanInventaris::find($id_peminjaman_inventaris);
        if (!$peminjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }
        
        $detail = DetailPeminjamanInventaris::with('inventaris.jenisInventaris')->where('id_peminjaman_inventaris', $id_peminjaman_inventaris)->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Detail peminjaman berhasil diambil',
            'data' => [
                'peminjaman' => $peminjaman,
                'detail' => $detail
            ]
        ]);
    }

   public function editPeminjamanInventaris(Request $request, $id_peminjaman_inventaris) {
    $validator = Validator::make($request->all(), [
        'id_peminjam' => 'required',
        'tipe_peminjam' => 'required|in:mahasiswa,dosen',
        'kegunaan' => 'required',
        'tanggal_pinjam' => 'required|date',
        'id_inventaris' => 'required|array',
        'id_inventaris.*' => 'required|exists:inventaris,id_inventaris',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error Validation',
            'errors' => $validator->errors()
        ], 400);
    }

    $peminjaman = PeminjamanInventaris::find($id_peminjaman_inventaris);
    if (!$peminjaman) {
        return response()->json([
            'status' => 'error',
            'message' => 'peminjaman tidak ditemukan',
        ], 404);
    }

    if ($peminjaman->status_peminjaman !== 'dipinjam') {
        return response()->json([
            'status' => 'error',
            'message' => 'hanya peminjaman yang belum dikembalikan yang dapat diedit',
        ], 400);
    }

    // cek jika ada inventaris yang sudah dikembalikan
    if (DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
        ->where('status', 1)->exists()) {
        return response()->json([
            'status' => 'error',
            'message' => 'tidak dapat mengedit peminjaman yang sudah dikembalikan',
        ], 400);
    }

    $user = $request->tipe_peminjam === 'mahasiswa' 
        ? User::find($request->id_peminjam)
        : Dosen::find($request->id_peminjam);
    
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'peminjam tidak ditemukan',
        ], 404);
    }

    if (date('Y-m-d', strtotime($request->tanggal_pinjam)) > date('Y-m-d')) {
        return response()->json([
            'status' => 'error',
            'message' => 'peminjaman tidak boleh lebih dari hari ini',
        ], 400);
    }

    // menyimpan data lama untuk notifikasi
    try {
        $peminjam_lama = $peminjaman->nama_peminjam;
        $inventaris_lama = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
            ->pluck('id_inventaris')->toArray();
        
        $peminjaman->update([
            'id_peminjam' => $request->id_peminjam,
            'nama_peminjam' => $user->name,
            'tipe_peminjam' => $request->tipe_peminjam,
            'ni_peminjam' => $request->tipe_peminjam === 'mahasiswa' ? $user->nim : $user->nik,
            'no_telp' => $user->no_telp,
            'kegunaan' => $request->kegunaan,
            'tanggal_pinjam' => $request->tanggal_pinjam,
        ]);

        // mengganti inventaris
        $inventaris_baru = $request->id_inventaris;
        $remove_inventaris = array_diff($inventaris_lama, $inventaris_baru);
        $added_inventaris = array_diff($inventaris_baru, $inventaris_lama);

        // mengupdate data inventaris yang di edit
        foreach ($remove_inventaris as $inventaris_id) {
            DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
                ->where('id_inventaris', $inventaris_id)->delete();
            
            Inventaris::find($inventaris_id)->update(['status' => 1]);
        }

        foreach ($added_inventaris as $inventaris_id) {
            if ($inventaris = Inventaris::where('id_inventaris', $inventaris_id)
                ->where('status', 1)->first()) {
                
                DetailPeminjamanInventaris::create([
                    'id_peminjaman_inventaris' => $id_peminjaman_inventaris,
                    'id_inventaris' => $inventaris_id,
                ]);
                
                $inventaris->update(['status' => 0]);
            }
        }

        // notif telegram
        $notif = ["Perubahan peminjaman ref {$peminjaman->referensi}"];
        
        $hitung_perubahan = count($inventaris_lama) != count($inventaris_baru);
        $perubahan_peminjam = $peminjam_lama !== $user->name;
        
        if ($hitung_perubahan) {
            $notif[] = "peminjaman ".count($inventaris_lama)." inventaris menjadi ".count($inventaris_baru)." inventaris";
        }
        
        if ($perubahan_peminjam) {
            $notif[] = "peminjam {$peminjam_lama} menjadi {$user->name}";
        }
        
        $notif[] = "diubah oleh ".auth()->user()->name;
        
        try {
            $this->sendTelegramMessage(
                env('TELEGRAM_CHAT_ID'), 
                implode($hitung_perubahan && $perubahan_peminjam ? '. ' : ' ', $notif)
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to send notification: '.$e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'peminjaman berhasil diperbarui',
            'data' => $peminjaman
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'peminjaman gagal diperbarui',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function pengembalianPeminjamanInventaris(Request $request, $id_peminjaman_inventaris) {
    $validator = Validator::make($request->all(), [
        'id_inventaris' => 'required|array',
        'id_inventaris.*' => 'required|exists:inventaris,id_inventaris',
        'catatan_pengembalian' => 'nullable|string',
        'kondisi_akhir_pengembalian' => 'required|in:baik,rusak,hilang',
        'tanggal_kembali' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error Validation',
            'errors' => $validator->errors()
        ], 400);
    }

    $peminjaman = PeminjamanInventaris::find($id_peminjaman_inventaris);
    if (!$peminjaman) {
        return response()->json([
            'status' => 'error',
            'message' => 'Peminjaman tidak ditemukan',
        ], 404);
    }

    // cari detail item peminjaman
    $detail_items = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
        ->whereIn('id_inventaris', $request->id_inventaris)
        ->get();

    if (count($detail_items) !== count($request->id_inventaris)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Beberapa inventaris tidak terkait dengan peminjaman ini',
        ], 400);
    }

    $dikembalikan = [];
    $sudah_dikembalikan = [];
    $tanggal_kembali = $request->tanggal_kembali;
    $catatan_pengembalian = $request->catatan_pengembalian;
    $kondisi_akhir_pengembalian = $request->kondisi_akhir_pengembalian;

    try {
        // proses pengembalian beserta update status inventaris
        foreach ($detail_items as $detail_item) {
            if ($detail_item->status == 1) {
                $sudah_dikembalikan[] = $detail_item->id_inventaris;
                continue;
            }
            
            $detail_item->status = 1;
            $detail_item->tanggal_kembali = $tanggal_kembali;
            $detail_item->catatan_pengembalian = $catatan_pengembalian;
            $detail_item->kondisi_akhir_pengembalian = $kondisi_akhir_pengembalian;
            $detail_item->save();
            
            $inventaris = Inventaris::find($detail_item->id_inventaris);
            if ($inventaris) {
                $inventaris->status = 1;
                $inventaris->kondisi = $kondisi_akhir_pengembalian;
                $inventaris->save();
            }
            
            $dikembalikan[] = $detail_item;
        }
        
        if (count($sudah_dikembalikan) > 0 && count($dikembalikan) == 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Semua item yang dipilih sudah dikembalikan sebelumnya',
                'data' => [
                    'item_sudah_dikembalikan' => $sudah_dikembalikan
                ]
            ], 200);
        }

        $total_item = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
            ->count();
            
        $jumlah_dikembalikan = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
            ->where('status', 1)
            ->count();

        // ubah status peminjaman
        if ($jumlah_dikembalikan == $total_item) {
            $peminjaman->status_peminjaman = 'selesai';
        } else if ($jumlah_dikembalikan > 0) {
            $peminjaman->status_peminjaman = 'dikembalikan_sebagian';
        }
        $peminjaman->save();

        if (count($dikembalikan) > 0) {
            try {
                $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Pengembalian " . count($dikembalikan) . " item inventaris oleh " . $peminjaman->nama_peminjam . ". Status peminjaman: " . $peminjaman->status_peminjaman);
            } catch (\Exception $e) {
                \Log::warning('Failed to send Telegram notification: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => count($dikembalikan) . ' item inventaris berhasil dikembalikan',
            'data' => [
                'item_dikembalikan' => $dikembalikan,
                'peminjaman_status' => $peminjaman->status_peminjaman
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal memproses pengembalian',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    public function deletePeminjamanInventaris($id_peminjaman_inventaris)
    {
        try {
            $peminjaman = PeminjamanInventaris::find($id_peminjaman_inventaris);
            
            if (!$peminjaman) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peminjaman tidak ditemukan'
                ], 404);
            }

            // cek jika ada peminjaman sudah dikembalikan
            $dikembalikan = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
                ->where('status', 1)
                ->exists();

            if ($dikembalikan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus peminjaman karena sudah ada barang yang dikembalikan'
                ], 400);
            }

            $detail = DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
                ->get();

            $jumlah_item = count($detail);
            $peminjam = $peminjaman->nama_peminjam;
            $no_ref = $peminjaman->referensi;

            DB::beginTransaction();

            try {
                // update status inventaris dan delete peminjaman
                foreach ($detail as $item) {
                    Inventaris::where('id_inventaris', $item->id_inventaris)
                        ->update(['status' => 1]);
                }

                DetailPeminjamanInventaris::where('id_peminjaman_inventaris', $id_peminjaman_inventaris)
                    ->delete();

                $peminjaman->delete();

                DB::commit();

                $notif = "Penghapusan peminjaman ref {$no_ref}: " .
                                    "{$jumlah_item} inventaris oleh {$peminjam} " .
                                    "dihapus oleh " . auth()->user()->name;

                try {
                    $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), $notif);
                } catch (\Exception $e) {
                    \Log::error('Gagal mengirim notifikasi Telegram: ' . $e->getMessage());
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Peminjaman berhasil dihapus',
                    'data' => [
                        'referensi' => $no_ref,
                        'jumlah_inventaris' => $jumlah_item,
                        'peminjam' => $peminjam
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus peminjaman',
                'error' => $e->getMessage()
            ], 500);
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
