<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Dosen;
use App\Models\Kategori;
use App\Models\Bimbingan;
use App\Models\TugasAkhir;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BimbinganResource;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BimbinganController extends Controller
{


    // ========================== FITUR MAHASISWA ==========================

    public function storeBimbinganByMahasiswa(Request $request)
    {
        try {
            $mahasiswa = Auth::id();
            $validated = $request->validate([
                'catatan' => 'required|string',
                'bab' => 'required|integer',
                'tanggal_bimbingan' => 'required|date|before_or_equal:today'
            ]);

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->select('id')
                ->firstOrFail();

            // Cek apakah ada bimbingan sebelumnya yang belum diverifikasi
            $previousBimbingan = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
                ->where('status_verifikasi', 0)
                ->exists();

            if ($previousBimbingan) {
                return ApiResponse::error('Tidak dapat membuat bimbingan baru karena bimbingan sebelumnya belum diverifikasi', 400);
            }

            $bimbingan = Bimbingan::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'catatan' => $validated['catatan'],
                'bab' => $validated['bab'],
                'tanggal_bimbingan' => $validated['tanggal_bimbingan']
            ]);

            return ApiResponse::success(
                new BimbinganResource($bimbingan->load(['tugasAkhir'])),
                'Bimbingan berhasil ditambahkan',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Bimbingan gagal ditambahkan ' . $e->getMessage(), 500);
        }
    }

    public function updateBimbinganByMahasiswa(Request $request, $id)
    {
        try {
            $mahasiswa = Auth::id();
            $validated = $request->validate([
                'catatan' => 'required|string',
                'bab' => 'required|integer',
                'tanggal_bimbingan' => 'required|date'
            ]);

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->select('id')
                ->firstOrFail();

            $bimbingan = Bimbingan::where('id', $id)
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->firstOrFail();


            // Cek apakah bimbingan sudah diverifikasi
            if ($bimbingan->status_verifikasi != 0) {
                return ApiResponse::error('Bimbingan sudah diverifikasi dan tidak dapat diubah', 400);
            }

            $bimbingan->update($validated);

            return ApiResponse::success(
                new BimbinganResource($bimbingan->load(['tugasAkhir'])),
                'Data bimbingan berhasil diubah'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Bimbingan gagal diubah: ' . $e->getMessage(), 500);
        }
    }

    public function showBimbinganForMahasiswa(Request $request)
    {
        try {
            Carbon::setLocale('id');
            $mahasiswa = Auth::id();

            $tugasAkhir = TugasAkhir::with('nilaiBimbingan')
                ->where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->select('id', 'nilai_bimbingan_id')
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::success([], 'Belum ada tugas akhir yang aktif');
            }

            // 1. Ambil SEMUA bimbingan untuk perhitungan nomor bimbingan_ke yang akurat
            $allBimbingan = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
                ->orderBy('id', 'asc')
                ->get(['id', 'status_verifikasi']);

            // 2. Buat mapping ID bimbingan => nomor bimbingan_ke
            $bimbinganKeMapping = [];
            $counter = 1; // Mulai dari 1 untuk bimbingan pertama

            foreach ($allBimbingan as $bimbingan) {
                $bimbinganKeMapping[$bimbingan->id] = $counter;

                // Hanya increment counter jika bimbingan disetujui (status = 1)
                if ($bimbingan->status_verifikasi == 1) {
                    $counter++;
                }
            }

            // 3. Ambil data dengan paginasi (tetap urut DESC agar terbaru muncul dulu)
            $bimbinganList = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
                ->orderBy('id', 'desc')
                ->paginate(10, ['id', 'tanggal_bimbingan', 'bab', 'catatan', 'status_verifikasi']);

            $babTertinggi = $tugasAkhir->bimbingan
                ->where('status_verifikasi', 1)
                ->max('bab') ?? null;

            // 4. Map data dengan nomor bimbingan_ke yang sudah dihitung
            $result = collect($bimbinganList->items())->map(function ($item) use ($bimbinganKeMapping) {
                return [
                    'id' => $item->id,
                    'bimbingan_ke' => $bimbinganKeMapping[$item->id], // Tambah bimbingan_ke dari mapping
                    'tanggal_bimbingan' => $item->tanggal_bimbingan
                        ? Carbon::parse($item->tanggal_bimbingan)->translatedFormat('l, d F Y')
                        : null,
                    'bab' => $item->bab,
                    'catatan' => $item->catatan,
                    'status_verifikasi' => $item->status_verifikasi,
                ];
            });

            $nilaiBimbingan = $tugasAkhir->nilaiBimbingan;
            if ($nilaiBimbingan) {
                $nilaiBimbingan = collect($nilaiBimbingan)->except(['created_at', 'updated_at']);
            }

            return ApiResponse::success([
                'bimbingan' => $result,
                'pagination' => [
                    'current_page' => $bimbinganList->currentPage(),
                    'last_page' => $bimbinganList->lastPage(),
                    'per_page' => $bimbinganList->perPage(),
                    'total' => $bimbinganList->total(),
                ],
                'bab_tertinggi' => $babTertinggi,
                'nilai_bimbingan' => $nilaiBimbingan
            ], 'Daftar bimbingan berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Daftar bimbingan gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function destroyBimbinganByMahasiswa($id)
    {
        try {
            $mahasiswa = Auth::id();

            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa)
                ->where('status_aktif', 1)
                ->select('id')
                ->firstOrFail();

            $bimbingan = Bimbingan::where('id', $id)
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->firstOrFail();

            // Cek apakah bimbingan sudah diverifikasi
            if ($bimbingan->status_verifikasi != 0) {
                return ApiResponse::error('Bimbingan sudah diverifikasi dan tidak dapat dihapus', 400);
            }

            $bimbingan->delete();
            return ApiResponse::success(null, 'Data bimbingan berhasil dihapus');
        } catch (Exception $e) {
            return ApiResponse::error('Bimbingan gagal dihapus: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR MAHASISWA ==========================


    // ========================== FITUR DOSEN ==========================

    public function tabelListDibimbingForDosen(Request $request)
    {
        try {
            $dosenId = Auth::id();

            $query = TugasAkhir::with([
                'mahasiswa:id,name,nim',
                'kategori:id,nama_kategori',
                'bimbingan'
            ])
                ->where('dosen_pembimbing_id', $dosenId);
            // ->where('status_aktif', 1);

            // Fitur search nim/nama mahasiswa
            if ($request->search) {
                $query->whereHas('mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                });
            }

            // Fitur filter kategori
            if ($request->kategori) {
                $kategoriList = explode(',', $request->kategori);
                $query->whereHas('kategori', function ($q) use ($kategoriList) {
                    $q->where(function ($subQuery) use ($kategoriList) {
                        foreach ($kategoriList as $index => $kategori) {
                            $kategori = trim($kategori);
                            if ($index === 0) {
                                $subQuery->where('nama_kategori', 'like', '%' . $kategori . '%');
                            } else {
                                $subQuery->orWhere('nama_kategori', 'like', '%' . $kategori . '%');
                            }
                        }
                    });
                });
            }

            $tugasAkhir = $query->paginate(10);

            $data = collect($tugasAkhir->items())->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                    ],
                    'kategori' => [
                        'kategori_id' => $item->kategori->id ?? null,
                        'nama_kategori' => $item->kategori->nama_kategori ?? null,
                    ],
                    'jumlah_bimbingan' => $item->bimbingan ? $item->bimbingan->where('status_verifikasi', 1)->count() : 0,
                ];
            });

            $result = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data tugas akhir yang dibimbing dosen ini berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Gagal mengambil data bimbingan: ' . $e->getMessage(), 500);
        }
    }

    public function tabelDetailBimbinganForDosen($tugasAkhirId)
    {
        try {
            $dosenId = Auth::id();

            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,name,nim',
                'kategori:id,nama_kategori',
                'bimbingan',
                'tahunAjaran:id,tahun_ajaran',
                'nilaiBimbingan:id,rerata_bimbingan,disiplin,komunikasi,skill_pengetahuan,tingkah_laku,catatan_akhir_bimbingan'
            ])
                ->where('id', $tugasAkhirId)
                ->where('dosen_pembimbing_id', $dosenId)
                ->first();

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir ?? null,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'bimbingan' => BimbinganResource::collection($tugasAkhir->bimbingan),
                'bab_tertinggi' => $tugasAkhir->bimbingan->max('bab'),
                'nilai_bimbingan' => $tugasAkhir->nilaiBimbingan
            ];

            return ApiResponse::success($data, 'Data bimbingan tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateVerifikasiBimbinganByDosen(Request $request, $bimbinganId)
    {
        $dosenId = Auth::id();

        try {

            $validated = $request->validate([
                'status_verifikasi' => 'required|integer|in:0,1,2',
            ]);

            // Mencari data bimbingan berdasarkan id yang dipilih dosen
            $bimbingan = Bimbingan::whereHas(
                'tugasAkhir',
                function ($query) use ($dosenId) {
                    $query->where('dosen_pembimbing_id', $dosenId);
                }
            )
                ->where('id', $bimbinganId)
                ->firstOrFail();

            $bimbingan->update($validated);
            $bimbingan->load(['tugasAkhir.dosenPembimbing']);

            return ApiResponse::success(
                new BimbinganResource($bimbingan),
                'Status verifikasi berhasil diubah'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Status verifikasi gagal diubah ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DOSEN ==========================

}