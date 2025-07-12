<?php

namespace App\Http\Controllers\Admin\AdminPengelola;

use App\Exports\PeminjamanBhpExport;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Foto;
use App\Models\Dosen;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\AdminPengelola\PeminjamanBhp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

class PeminjamanBhpController extends Controller
{
    public function showPeminjamanBhp(Request $request) {
        $query = PeminjamanBhp::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_peminjam', 'like', "%{$searchTerm}%")
                  ->orWhere('ni_peminjam', 'like', "%{$searchTerm}%")
                  ->orWhere('kegunaan', 'like', "%{$searchTerm}%")
                  ->orWhereHas('bhp', function($query) use ($searchTerm) {
                    $query->where('nama_bhp', 'like', "%{$searchTerm}%");
                });
            }
        );
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

            if ($sortColumn === 'bhp.nama_bhp') {
                $query->join('bhps', 'peminjaman_bhps.id_bhp', '=', 'bhps.id_bhp')
                    ->select('peminjaman_bhps.*', 'bhps.nama_bhp');
                
                $sortColumn = 'bhps.nama_bhp';
            }
        } else {
            $sortColumn = 'created_at';
        }

        $sortDirection = in_array(strtolower($sortType), ['asc', 'desc']) ? $sortType : 'desc';

        $query->orderBy($sortColumn, $sortDirection);
            $peminjaman_bhp = $query->get();
                
            $peminjaman_bhp = $peminjaman_bhp->map(function($peminjaman) {
                $photos = Foto::where('id_bhp', $peminjaman->id_bhp)
                            ->select('foto_path')
                            ->get();
                    
                $peminjaman->fotos = $photos;

                if ($peminjaman->bhp) {
                    $peminjaman->nama_bhp = $peminjaman->bhp->nama_bhp;
                } else {
                    $peminjaman->nama_bhp = 'BHP tidak ditemukan';
                }
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
                ],
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_type' => $sortType,
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
            'tanggal_pengembalian' => 'required',
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
                'errors' => [
                    'tanggal_pinjam' => ['Tanggal pinjam tidak boleh lebih dari hari ini'],
                ],
            ], 400);
        }

        $tanggal_pengembalian = date('Y-m-d', strtotime($request->tanggal_pengembalian));
        if ($tanggal_pengembalian < $tanggal_pinjam) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal pengembalian tidak boleh sebelum tanggal pinjam',
                'errors' => [
                    'tanggal_pengembalian' => ['Tanggal Pengembalian tidak boleh sebelum tanggal pinjam'],
                ],
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
                'tanggal_pengembalian' => $request->tanggal_pengembalian,
                'jumlah_penggunaan' => $request->jumlah_penggunaan,
                'status_penggunaan' => 'dipinjam',
                'jumlah_kembali_bagus' => 0,
                'jumlah_kembali_rusak' => 0,
                'keterangan_pengembalian' => null,
                'created_by' => Auth()->user()->name,
            ]);

            DB::commit();


            $url = env('FRONTEND_URL') . '/admin-pengelola/peminjaman-bhp/detail/' . $peminjaman_bhp->id_peminjaman_bhp;

            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeContent = $writer->writeString($url);

            $qrFileName = Str::uuid() . '.svg';
            $qrFilePath = "public/bhp/peminjaman/qrcode/{$qrFileName}";
            Storage::put($qrFilePath, $qrCodeContent);

            $peminjaman_bhp->qr_code_path = $qrFileName;
            $peminjaman_bhp->save();

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

    public function findPeminjamanBhp($id, Request $request) {
        $peminjaman_bhp = PeminjamanBhp::find($id);
        if (!$peminjaman_bhp)  {
            return response()->json(['message' => 'Peminjaman BHP tidak ditemukan'], 404);
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

    public function editPeminjamanBhp(Request $request, $id) {
    $validator = Validator::make($request->all(), [
        'id_bhp' => 'required',
        'id_peminjam' => 'required',
        'tipe_peminjam' => 'required|in:mahasiswa,dosen',
        'kegunaan' => 'required',
        'tanggal_pinjam' => 'required',
        'tanggal_pengembalian' => 'required',
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

    if ($peminjaman_bhp->status_pengembalian != 'dipinjam') {
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
    $tanggal_pengembalian = date('Y-m-d', strtotime($request->tanggal_pengembalian));
    if ($tanggal_pengembalian < $tanggal_pinjam) {
        return response()->json([
            'status' => 'error',
            'message' => 'Tanggal pengembalian tidak boleh sebelum tanggal pinjam',
            'errors' => [
                'tanggal_pengembalian' => ['Tanggal Pengembalian tidak boleh sebelum tanggal pinjam'],
            ],
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
                'tanggal_pengembalian' => $request->tanggal_pengembalian,
                'jumlah_penggunaan' => $jumlah_baru,
            ]);

            DB::commit();

            try {
                $this->sendTelegramMessage(env('TELEGRAM_CHAT_ID'), 
                    "Peminjaman BHP diperbarui: " . $jumlah_baru . " item oleh " . 
                    $peminjaman_bhp->nama_peminjam . " pada tanggal " . $request->tanggal_pinjam . ". " .
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

        if ($peminjaman_bhp->status_pengembalian === 'selesai') {
            return response()->json([
                'message' => 'Peminjaman BHP sudah dikembalikan'
            ], 400);
        }

        $jumlah_kembali_total = $request->jumlah_kembali_bagus + $request->jumlah_kembali_rusak;
        $sisa_belum_kembali = $peminjaman_bhp->jumlah_penggunaan - 
                            ($peminjaman_bhp->jumlah_kembali_bagus + $peminjaman_bhp->jumlah_kembali_rusak);

        if ($jumlah_kembali_total > $sisa_belum_kembali) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah pengembalian melebihi jumlah yang belum dikembalikan',
                'sisa_belum_kembali' => $sisa_belum_kembali
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

            $total_kembali_bagus = $peminjaman_bhp->jumlah_kembali_bagus + $request->jumlah_kembali_bagus;
            $total_kembali_rusak = $peminjaman_bhp->jumlah_kembali_rusak + $request->jumlah_kembali_rusak;
            $total_kembali = $total_kembali_bagus + $total_kembali_rusak;

            $new_status = 'dipinjam';
            if ($total_kembali === $peminjaman_bhp->jumlah_penggunaan) {
                $new_status = 'selesai';
            } elseif ($total_kembali > 0) {
                $new_status = 'dikembalikan_sebagian';
            }

            $peminjaman_bhp->status_pengembalian = $new_status;
            $peminjaman_bhp->jumlah_kembali_bagus = $total_kembali_bagus;
            $peminjaman_bhp->jumlah_kembali_rusak = $total_kembali_rusak;
            $peminjaman_bhp->tanggal_kembali = $request->tanggal_kembali;
            
            $keterangan_baru = $request->keterangan_pengembalian;
            if ($peminjaman_bhp->keterangan_pengembalian) {
                $keterangan_baru = $peminjaman_bhp->keterangan_pengembalian . "\n" . 
                                 ",   " . $keterangan_baru;
            }
            $peminjaman_bhp->keterangan_pengembalian = $keterangan_baru;
            
            $peminjaman_bhp->save();

            DB::commit();

            $status_text = $new_status === 'selesai' ? 'sepenuhnya' : 
                ($new_status === 'dikembalikan_sebagian' ? 'sebagian' : '');
            
            $this->sendTelegramMessage(
                env('TELEGRAM_CHAT_ID'), 
                "Peminjaman BHP: {$jumlah_kembali_total} item dari total {$peminjaman_bhp->jumlah_penggunaan} " .
                "oleh {$peminjaman_bhp->nama_peminjam} telah dikembalikan {$status_text} " .
                "pada tanggal {$request->tanggal_kembali}. " .
                "Status saat ini: {$new_status}. " .
                "Dikembalikan oleh: " . auth()->user()->name
            );
                return response()->json([
                    'message' => 'Peminjaman BHP berhasil dikembalikan ',
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

        if ($peminjaman->status_pengembalian !== 'dipinjam') {
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

    public function exportPeminjamanBhp(Request $request) {
        $searchTerm = $request->input('search', null);
        $status_pengembalian = $request->input('status_pengembalian', null);
        $start_date = $request->input('start_date', null);
        $end_date = $request->input('end_date', null);
        
        return Excel::download(
            new PeminjamanBhpExport($searchTerm, $status_pengembalian, $start_date, $end_date),
            'peminjaman_bhp.xlsx'
        );
    }

    public function sendOverdueReminders() {
        \Log::info('Memulai proses pengiriman pengingat peminjaman BHP terlambat');
        
        // Ambil semua peminjaman yang belum dikembalikan dan melewati tanggal pengembalian
        $peminjaman_terlambat = PeminjamanBhp::where('status_pengembalian', 'dipinjam')
            ->where('tanggal_pengembalian', '<', now()->format('Y-m-d'))
            ->with(['bhp'])
            ->get();
        
        if ($peminjaman_terlambat->count() == 0) {
            \Log::info('Tidak ada peminjaman BHP yang melewati tanggal pengembalian');
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada peminjaman BHP yang melewati tanggal pengembalian'
            ]);
        }
        
        $pengingat_terkirim = 0;
        
        foreach ($peminjaman_terlambat as $peminjaman) {
            $tanggal_harus_kembali = Carbon::parse($peminjaman->tanggal_pengembalian);
            $hari_terlambat = now()->diffInDays($tanggal_harus_kembali);
            
            // Cek jumlah yang belum dikembalikan
            $jumlah_belum_kembali = $peminjaman->jumlah_penggunaan - 
                                    ($peminjaman->jumlah_kembali_bagus + $peminjaman->jumlah_kembali_rusak);
            
            if ($jumlah_belum_kembali > 0) {
                $email_peminjam = null;
                
                if ($peminjaman->tipe_peminjam === 'mahasiswa') {
                    $mahasiswa = User::find($peminjaman->id_peminjam);
                    if ($mahasiswa) {
                        $email_peminjam = $mahasiswa->email;
                    }
                } else if ($peminjaman->tipe_peminjam === 'dosen') {
                    $dosen = Dosen::find($peminjaman->id_peminjam);
                    if ($dosen) {
                        $email_peminjam = $dosen->email;
                    }
                }
                
                if ($email_peminjam) {
                    $subject = "Pengingat Peminjaman BHP Terlambat";
                    
                    // Data untuk view email
                    $data = [
                        'peminjaman' => $peminjaman,
                        'hari_terlambat' => $hari_terlambat,
                        'jumlah_belum_kembali' => $jumlah_belum_kembali,
                        'tanggal_kembali' => isset($peminjaman->tanggal_pengembalian) ? 
                                            Carbon::parse($peminjaman->tanggal_pengembalian)->format('d-m-Y') : 'Tidak ditentukan',
                        'bhp' => $peminjaman->bhp
                    ];
                    
                    try {
                        Mail::send('emails.reminder_bhp', $data, function($message) use ($email_peminjam, $subject, $peminjaman) {
                            $message->to($email_peminjam)
                                    ->subject($subject . ': BHP-' . $peminjaman->id_peminjaman_bhp);
                        });
                        
                        $pengingat_terkirim++;
                        
                        \Log::info("Pengingat keterlambatan BHP berhasil dikirim ke {$email_peminjam} untuk peminjaman {$peminjaman->id_peminjaman_bhp}");
                        
                    } catch (\Exception $e) {
                        \Log::error('Gagal mengirim pengingat email BHP: ' . $e->getMessage());
                        \Log::error($e->getTraceAsString());
                    }
                } else {
                    \Log::warning("Email peminjam BHP tidak ditemukan untuk ID: {$peminjaman->id_peminjam}, Tipe: {$peminjaman->tipe_peminjam}");
                }
            }
        }
        
        \Log::info("Total pengingat BHP terkirim: {$pengingat_terkirim}");
        
        return response()->json([
            'status' => 'success',
            'message' => "Berhasil mengirim {$pengingat_terkirim} pengingat untuk peminjaman BHP terlambat"
        ]);
    }

    public function manualSendOverdueReminders()
    {
        return $this->sendOverdueReminders();
    }

}
