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
use Illuminate\Support\Facades\Auth;
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

        $peminjaman_bhp = $query->get();
        
        $peminjaman_bhp = $peminjaman_bhp->map(function($peminjaman) {
            $photos = Foto::where('id_bhp', $peminjaman->id_bhp)
                         ->select('foto_path')
                         ->get();
            
            $peminjaman->fotos = $photos;
            return $peminjaman;
        });
    
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
            
            $peminjaman_bhp = PeminjamanBhp::create([
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
                'created_by' => Auth()->user()->name,
            ]);

            DB::commit();

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman Bhp $request->jumlah_penggunaan item  oleh " . $user->name . "-" . $nomor_identitas . " pada tanggal " . $request->tanggal_pinjam . ". Peminjaman dibuat oleh " . auth()->user()->name);

            return response()->json([
                'message' => 'Peminjaman BHP berhasil ditambahkan',
                'peminjaman' => $peminjaman_bhp
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
        $peminjaman_bhp = PeminjamanBhp::find($id);
        if ($peminjaman_bhp) {
            return response()->json($peminjaman_bhp);
        } else {
            return response()->json(['message' => 'Peminjaman BHP tidak ditemukan'], 404);
        }
    }

    public function editPeminjamanBhp(Request $request, $id) {
    $validator = Validator::make($request->all(), [
        'id_bhp' => 'required',
        'id_peminjam' => 'required',
        'tipe_peminjam' => 'required|in:mahasiswa,dosen',
        'kegunaan' => 'required',
        'tanggal_pinjam' => 'required',
        'jumlah_penggunaan' => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validasi Error',
            'errors' => $validator->errors()
        ], 400);
    }

    $peminjaman_bhp = PeminjamanBhp::find($id);
    if (!$peminjaman_bhp) {
        return response()->json([
            'status' => 'error',
            'message' => 'Peminjaman BHP tidak ditemukan'
        ], 404);
    }

    if ($peminjaman_bhp->status_pengembalian == 1) {
        return response()->json([
            'status' => 'error',
            'message' => 'Peminjaman BHP yang sudah dikembalikan tidak dapat diedit'
        ], 400);
    }

    $pengguna = null;
    $nomor_identitas = null;

    if ($request->tipe_peminjam === 'mahasiswa') {
        $pengguna = User::find($request->id_peminjam);
        if ($pengguna) {
            $nomor_identitas = $pengguna->nim;
        }
    } elseif ($request->tipe_peminjam === 'dosen') {
        $pengguna = Dosen::find($request->id_peminjam);
        if ($pengguna) {
            $nomor_identitas = $pengguna->nik;
        }
    }

    if (!$pengguna) {
        return response()->json([
            'status' => 'error',
            'message' => ucfirst($request->tipe_peminjam) . ' tidak ditemukan'
        ], 404);
    }

    // cek apakah BHP ada
    $bhp_baru = Bhp::find($request->id_bhp);
    if (!$bhp_baru) {
        return response()->json([
            'status' => 'error',
            'message' => 'BHP tidak ditemukan'
        ], 404);
    }

    $bhp_lama = Bhp::find($peminjaman_bhp->id_bhp);
    if (!$bhp_lama) {
        return response()->json([
            'status' => 'error',
            'message' => 'BHP lama tidak ditemukan'
        ], 404);
    }

    // jumlah
    $jumlah_baru = $request->jumlah_penggunaan;
    $jumlah_lama = $peminjaman_bhp->jumlah_penggunaan;
    
    $tanggal_pinjam = date('Y-m-d', strtotime($request->tanggal_pinjam));
    $sekarang = date('Y-m-d');
    if ($tanggal_pinjam > $sekarang) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tanggal pinjam tidak boleh lebih dari hari ini',
        ], 400);
    }

    DB::beginTransaction();
        try {
            // jika data bhp berubah maka stok dikembalikan
            if ($bhp_lama->id_bhp != $bhp_baru->id_bhp) {
                $bhp_lama->kondisi_bagus += $jumlah_lama;
                $bhp_lama->save();
                
                // cek stok BHP apabila ada maka dikurangi 
                if ($jumlah_baru > $bhp_baru->kondisi_bagus) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Jumlah penggunaan melebihi stok BHP baru',
                        'stok_tersedia' => $bhp_baru->kondisi_bagus
                    ], 422);
                }
                
                $bhp_baru->kondisi_bagus -= $jumlah_baru;
                $bhp_baru->save();
            } else {
                // apabila bhp sama dan jumlah berubah
                $perubahan_jumlah = $jumlah_lama - $jumlah_baru;
                
                if ($perubahan_jumlah < 0) {
                    // $tambahan_stok = abs($perubahan_jumlah);
                    $tambahan_stok = -$perubahan_jumlah;
                    
                    if ($tambahan_stok > $bhp_lama->kondisi_bagus) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Penambahan jumlah melebihi stok tersedia',
                            'stok_tersedia' => $bhp_lama->kondisi_bagus
                        ], 422);
                    }
                    // cek jumlah peminjaman apabila lebih banyak maka stok -
                    
                    $bhp_lama->kondisi_bagus -= $tambahan_stok;
                } else {
                    $bhp_lama->kondisi_bagus += $perubahan_jumlah;
                }
                
                $bhp_lama->save();
            }

            $peminjaman_bhp->update([
                'id_bhp' => $request->id_bhp,
                'id_peminjam' => $request->id_peminjam,
                'tipe_peminjam' => $request->tipe_peminjam,
                'nama_peminjam' => $pengguna->name,
                'ni_peminjam' => $nomor_identitas,
                'no_telp' => $pengguna->no_telp,
                'kegunaan' => $request->kegunaan,
                'tanggal_pinjam' => $request->tanggal_pinjam,
                'jumlah_penggunaan' => $jumlah_baru,
            ]);

            DB::commit();

            try {
                $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), 
                    "Peminjaman BHP diperbarui: " . $jumlah_baru . " item oleh " . 
                    $pengguna->name . " pada tanggal " . $request->tanggal_pinjam . ". " .
                    "Peminjaman diedit oleh " . auth()->user()->name
                );
            } catch (\Exception $e) {
                \Log::warning('Gagal mengirim notifikasi Telegram: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman BHP berhasil diperbarui',
                'data' => $peminjaman_bhp
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui peminjaman BHP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function returnPeminjamanBhp(Request $request, $id) {
        $peminjaman_bhp = PeminjamanBhp::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jumlah_kembali_bagus' => 'required',
            'jumlah_kembali_rusak' => 'required',
            'tanggal_kembali' => 'required',
            'keterangan_pengembalian' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($peminjaman_bhp->status_pengembalian === 1) {
            return response()->json([
                'message' => 'Peminjaman BHP sudah dikembalikan'
            ], 400);
        }

        if ($request->jumlah_kembali_bagus > $peminjaman_bhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali bagus melebihi jumlah penggunaan',
                'jumlah_penggunaan' => $peminjaman_bhp->jumlah_penggunaan
            ], 422);
        }

        if ($request->jumlah_kembali_rusak > $peminjaman_bhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali rusak melebihi jumlah penggunaan',
                'jumlah_penggunaan' => $peminjaman_bhp->jumlah_penggunaan
            ], 422);
        }

        if ($request->jumlah_kembali_bagus + $request->jumlah_kembali_rusak !== $peminjaman_bhp->jumlah_penggunaan) {
            return response()->json([
                'message' => 'Jumlah kembali bagus dan rusak tidak sesuai dengan jumlah penggunaan'
            ], 422);
        }

        // cek tanggal kembali tidak boleh kurang dari tanggal pinjam
        $tanggal_kembali = date('Y-m-d', strtotime($request->tanggal_kembali));
        $tanggal_pinjam = date('Y-m-d', strtotime($peminjaman_bhp->tanggal_pinjam));
        if ($tanggal_kembali < $tanggal_pinjam) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal kembali tidak boleh kurang dari tanggal pinjam',
            ], 400);
        }
        // cek tanggal kembali tidak boleh lebih dari hari ini
        $sekarang = date('Y-m-d');
        if ($tanggal_kembali > $sekarang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal kembali tidak boleh lebih dari hari ini',
            ], 400);
        }


        DB::beginTransaction();
        try {
            $bhp = Bhp::find($peminjaman_bhp->id_bhp);
            $bhp->kondisi_bagus += $request->jumlah_kembali_bagus;
            $bhp->kondisi_rusak += $request->jumlah_kembali_rusak;
            $bhp->save();

            $peminjaman_bhp->status_pengembalian = 1;
            $peminjaman_bhp->jumlah_kembali_bagus = $request->jumlah_kembali_bagus;
            $peminjaman_bhp->jumlah_kembali_rusak = $request->jumlah_kembali_rusak;
            $peminjaman_bhp->tanggal_kembali = $request->tanggal_kembali;
            $peminjaman_bhp->keterangan_pengembalian = $request->keterangan_pengembalian;
            $peminjaman_bhp->save();

            DB::commit();

            $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), "Peminjaman Bhp {$peminjaman_bhp->jumlah_penggunaan}" . " oleh $peminjaman_bhp->nama_peminjam telah dikembalikan pada tanggal " . $request->tanggal_kembali . ". Peminjaman dikembalikan oleh " . auth()->user()->name);

            return response()->json([
                'message' => 'Peminjaman BHP berhasil dikembalikan',
                'peminjaman' => $peminjaman_bhp
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengembalikan peminjaman',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function deletePeminjamanBhp($id){
        $peminjaman = PeminjamanBhp::findOrFail($id);

        if ($peminjaman->status_pengembalian === 1) {
            return response()->json([
                'message' => 'Tidak dapat menghapus peminjaman yang sudah dikembalikan'
            ], 400);
        }

        DB::beginTransaction();
        // cari data bhp dan kembalikan stok
        try {
            $bhp = Bhp::find($peminjaman->id_bhp);
            
            $bhp->kondisi_bagus += $peminjaman->jumlah_penggunaan;
            $bhp->save();

            $peminjaman->delete();

            DB::commit();

            $this->sendTelegramMessage(
                env('TELEGRAM_CHAT_ID'), 
                "Peminjaman BHP " . "{$peminjaman->jumlah_penggunaan} item oleh {$peminjaman->nama_peminjam} " . " telah dihapus oleh: " . auth()->user()->name
            );

            return response()->json([
                'message' => 'Peminjaman BHP berhasil dihapus',
                'data' => [
                    'id' => $id,
                    'jumlah_penggunaan' => $peminjaman->jumlah_penggunaan,
                    'peminjam' => $peminjaman->nama_peminjam
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
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
