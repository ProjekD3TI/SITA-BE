<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Foto;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdminPengelola\PeminjamanBhp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


class PeminjamanBhpController extends Controller
{
    public function showPeminjamanBhp(Request $request) {
        $query = PeminjamanBhp::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_peminjam', 'like', "%{$searchTerm}%")
                  ->orWhere('ni_peminjam', 'like', "%{$searchTerm}%")
                  ->orWhere('kegunaan', 'like', "%{$searchTerm}%");
            }
        );
        }
        
        if ($request->has('status_pengembalian') && $request->status_pengembalian !== '') {
            $query->where('status_pengembalian', $request->status_pengembalian);
        }
        $query->orderBy('created_at', 'desc');

        $peminjamanBhp = $query->get();
        
        $peminjamanBhp = $peminjamanBhp->map(function($peminjaman) {
            $photos = Foto::where('id_bhp', $peminjaman->id_bhp)
                         ->select('foto_path')
                         ->get();
            
            $peminjaman->fotos = $photos;
            return $peminjaman;
        });
    
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
    
        $paginatedData = PaginationHelper::paginate($peminjamanBhp, $perPage, $page, [
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
            ]
        ], 200);
    }
    

    public function createPeminjamanBhp(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_bhp' => 'required',
            'id_peminjam' => 'required',
            'tipe_peminjam' => 'required|in:mahasiswa,dosen',
            'kegunaan' => 'required',
            'tanggal_pinjam' => 'required',
            'jumlah_penggunaan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = null;
        $nomor_identitas = null;
    
        if ($request->tipe_peminjam === 'mahasiswa') {
            $user = User::find($request->id_peminjam);
            if ($user) {
                $nomor_identitas = $user->nim;
            }
        } elseif ($request->tipe_peminjam === 'dosen') {
            $user = Dosen::find($request->id_peminjam);
            if ($user) {
                $nomor_identitas = $user->nik;
            }
        }

        if (!$user) {
            return response()->json([
                'message' => ucfirst($request->tipe_peminjam) . ' tidak ditemukan'
            ], 404);
        }

        $bhp = Bhp::find($request->id_bhp);
        if (!$bhp) {
            return response()->json(['message' => 'BHP tidak ditemukan'], 404);
        }

        if ($request->jumlah_penggunaan > $bhp->kondisi_bagus) {
            return response()->json([
                'message' => 'Jumlah penggunaan melebihi stok barang',
                'stok_tersedia' => $bhp->kondisi_bagus
            ], 422);
        }

        //cek apakah tanggal pinjam lebih dari hari ini
        $tanggal_pinjam = date('Y-m-d', strtotime($request->tanggal_pinjam));
        $sekarang = date('Y-m-d');
        if ($tanggal_pinjam > $sekarang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal pinjam tidak boleh lebih dari hari ini',
            ], 400);
        }

        // jumlah pengunaan = 0
        if ($request->jumlah_penggunaan <= 0) {
            return response()->json([
                'message' => 'Jumlah penggunaan minimal adalah 1'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $bhp->kondisi_bagus -= $request->jumlah_penggunaan;
            $bhp->save();
            
            $peminjamanBhp = PeminjamanBhp::create([
                'id_bhp' => $request->id_bhp,
                'id_peminjam' => $request->id_peminjam,
                'tipe_peminjam' => $request->tipe_peminjam,
                'nama_peminjam' => $user->name,
                'ni_peminjam' => $nomor_identitas,
                'no_telp' => $user->no_telp,
                'kegunaan' => $request->kegunaan,
                'tanggal_pinjam' => $request->tanggal_pinjam,
                'jumlah_penggunaan' => $request->jumlah_penggunaan,
                'status_penggunaan' => 0,
                'jumlah_kembali_bagus' => 0,
                'jumlah_kembali_rusak' => 0,
                'keterangan_pengembalian' => null,
            ]);

            DB::commit();

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman Bhp $request->jumlah_penggunaan item  oleh " . $user->name . "-" . $nomor_identitas . " pada tanggal " . $request->tanggal_pinjam . ". Peminjaman dibuat oleh " . auth()->user()->name);

            return response()->json([
                'message' => 'Peminjaman BHP berhasil ditambahkan',
                'peminjaman' => $peminjamanBhp
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan peminjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    
    }

    public function findPeminjamanBhp($id) {
        $peminjamanBhp = PeminjamanBhp::find($id);
        if ($peminjamanBhp) {
            return response()->json($peminjamanBhp);
        } else {
            return response()->json(['message' => 'Peminjaman BHP tidak ditemukan'], 404);
        }
    }

    // public function editPeminjamanBhp(Request $request, $id) {
    //     $validator = Validator::make($request->all(), [
    //         'id_bhp' => 'required',
    //         'id_peminjam' => 'required',
    //         'tipe_peminjam' => 'required|in:mahasiswa,dosen',
    //         'kegunaan' => 'required',
    //         'tanggal_pinjam' => 'required',
    //         'jumlah_penggunaan' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 400);
    //     }

    //     $peminjamanBhp = PeminjamanBhp::find($id);
    //     if (!$peminjamanBhp) {
    //         return response()->json(['message' => 'Data peminjaman tidak ditemukan'], 404);
    //     }
    //     if ($peminjamanBhp->status_pengembalian === 1) {
    //         return response()->json([
    //             'message' => 'Peminjaman BHP sudah dikembalikan'
    //         ], 400);
    //     }
        

    //     $user = null;
    //     $nomor_identitas = null;

    //     if ($request->tipe_peminjam === 'mahasiswa') {
    //         $user = User::find($request->id_peminjam);
    //         if ($user) {
    //             $nomor_identitas = $user->nim;
    //         }
    //     } elseif ($request->tipe_peminjam === 'dosen') {
    //         $user = Dosen::find($request->id_peminjam);
    //         if ($user) {
    //             $nomor_identitas = $user->nik;
    //         }
    //     }

    //     if (!$user) {
    //         return response()->json([
    //             'message' => ucfirst($request->tipe_peminjam) . ' tidak ditemukan'
    //         ], 404);
    //     }

    //     $bhp = Bhp::find($request->id_bhp);
    //     if (!$bhp) {
    //         return response()->json(['message' => 'BHP tidak ditemukan'], 404);
    //     }

    //     // hitung selisih jumlah penggunaan
    //     $jumlah_penggunaan_lama = $peminjamanBhp->jumlah_penggunaan;
    //     $jumlah_penggunaan_baru = $request->jumlah_penggunaan;
    //     $selisih = $jumlah_penggunaan_baru - $jumlah_penggunaan_lama;

    //     // cek kalo ada perubahan jumlah penggunaan dan stok yg ga cukup
    //     if ($selisih > 0) {
    //         if ($selisih > $bhp->kondisi_bagus) {
    //             return response()->json([
    //                 'message' => 'Stok BHP tidak mencukupi untuk penambahan jumlah peminjaman',
    //                 'stok_tersedia' => $bhp->kondisi_bagus,
    //                 'selisih_penambahan' => $selisih
    //             ], 422);
    //         }
    //     }

    //     // jumlah pengunaan = 0
    //     if ($jumlah_penggunaan_baru <= 0) {
    //         return response()->json([
    //             'message' => 'Jumlah penggunaan minimal adalah 1'
    //         ], 422);
    //     }

    //     //cek apakah tanggal pinjam lebih dari hari ini
    //     $tanggal_pinjam = date('Y-m-d', strtotime($request->tanggal_pinjam));
    //     $sekarang = date('Y-m-d');
    //     if ($tanggal_pinjam > $sekarang) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tanggal pinjam tidak boleh lebih dari hari ini',
    //         ], 400);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // update stok dr selisih
    //         if ($selisih != 0) {
    //             // klo positif kurangi klo negatif tambah
    //             $bhp->kondisi_bagus -= $selisih;
    //             $bhp->save();
    //         }
            
    //         $peminjamanBhp->update([
    //             'id_bhp' => $request->id_bhp,
    //             'id_peminjam' => $request->id_peminjam,
    //             'tipe_peminjam' => $request->tipe_peminjam,
    //             'nama_peminjam' => $user->name,
    //             'ni_peminjam' => $nomor_identitas,
    //             'no_telp' => $user->no_telp,
    //             'kegunaan' => $request->kegunaan,
    //             'tanggal_pinjam' => $request->tanggal_pinjam,
    //             'jumlah_penggunaan' => $jumlah_penggunaan_baru,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Peminjaman BHP berhasil diperbarui',
    //             'peminjaman' => $peminjamanBhp
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Gagal memperbarui peminjaman',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    
    public function returnPeminjamanBhp(Request $request, $id) {
        $peminjamanBhp = PeminjamanBhp::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jumlah_kembali_bagus' => 'required',
            'jumlah_kembali_rusak' => 'required',
            'tanggal_kembali' => 'required',
            'keterangan_pengembalian' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($peminjamanBhp->status_pengembalian === 1) {
            return response()->json([
                'message' => 'Peminjaman BHP sudah dikembalikan'
            ], 400);
        }

        if ($request->jumlah_kembali_bagus > $peminjamanBhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali bagus melebihi jumlah penggunaan',
                'jumlah_penggunaan' => $peminjamanBhp->jumlah_penggunaan
            ], 422);
        }

        if ($request->jumlah_kembali_rusak > $peminjamanBhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali rusak melebihi jumlah penggunaan',
                'jumlah_penggunaan' => $peminjamanBhp->jumlah_penggunaan
            ], 422);
        }

        if ($request->jumlah_kembali_bagus + $request->jumlah_kembali_rusak !== $peminjamanBhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali bagus dan rusak tidak sesuai dengan jumlah penggunaan'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $bhp = Bhp::find($peminjamanBhp->id_bhp);
            $bhp->kondisi_bagus += $request->jumlah_kembali_bagus;
            $bhp->kondisi_rusak += $request->jumlah_kembali_rusak;
            $bhp->save();

            $peminjamanBhp->status_pengembalian = 1;
            $peminjamanBhp->jumlah_kembali_bagus = $request->jumlah_kembali_bagus;
            $peminjamanBhp->jumlah_kembali_rusak = $request->jumlah_kembali_rusak;
            $peminjamanBhp->tanggal_kembali = $request->tanggal_kembali;
            $peminjamanBhp->keterangan_pengembalian = $request->keterangan_pengembalian;
            $peminjamanBhp->save();

            DB::commit();

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman Bhp oleh $peminjamanBhp->nama_peminjam telah dikembalikan pada tanggal " . $request->tanggal_kembali  );


            return response()->json([
                'message' => 'Peminjaman BHP berhasil dikembalikan',
                'peminjaman' => $peminjamanBhp
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengembalikan peminjaman',
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
