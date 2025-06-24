<?php

namespace App\Http\Controllers\Api;


use Exception;
use Carbon\Carbon;
use App\Models\Sesi;
use App\Models\Dosen;
use App\Models\Ruang;
use App\Models\UjianTA;
use App\Models\Bimbingan;
use App\Models\StatusForm;
use App\Models\TugasAkhir;
use App\Models\TahunAjaran;
use App\Models\NilaiUjianTA;
use Illuminate\Http\Request;
use App\Exports\UjianTAExport;
use App\Models\RequestPembimbing;
use App\Http\Responses\ApiResponse;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TugasAkhirResource;
use Illuminate\Validation\ValidationException;


class TugasAkhirController extends Controller
{
    public function tabelTugasAkhirForAdmin(Request $request)
    {
        try {
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'kategori:id,nama_kategori'
            ]);

            // Filter by status_aktif jika ada
            if ($request->has('status_aktif') && $request->status_aktif !== '') {
                $statusList = explode(',', $request->status_aktif);
                $statusList = array_map('trim', $statusList);
                $query->whereIn('status_aktif', $statusList);
            }

            // Search by nama mahasiswa atau judul tugas akhir
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('mahasiswa', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $search . '%');
                });
            }

            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }

            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);

            // Pagination
            $tugasAkhir = $query->orderByDesc('id')->paginate(10);

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'tahun_ajaran' => [
                        'id' => $item->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'mahasiswa' => [
                        'id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'kategori' => [
                        'id' => $item->kategori->id ?? null,
                        'name' => $item->kategori->nama_kategori ?? null,
                    ],
                    'status_aktif' => $item->status_aktif,
                ];
            });

            $result = [
                'data' => $data,
                'tahun_ajaran_list' => $tahunAjaranList,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function showTugasAkhirForAdmin($id)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,name',
                'semester:id,nama_semester',
                'tahunAjaran:id,tahun_ajaran',
                'dosenPembimbing:id,name',
                'berkas'
            ])->findOrFail($id);

            // Ambil hanya berkas naskah tugas akhir
            $naskahBerkas = $tugasAkhir->berkas
                ->where('nama_berkas', 'Naskah Tugas Akhir')
                ->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => $berkas ? route('berkas.download', ['berkas' => $berkas->id]) : null,
                    ];
                })
                ->values();

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'id' => $tugasAkhir->mahasiswa->id ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'semester' => [
                    'id' => $tugasAkhir->semester->id ?? null,
                    'name' => $tugasAkhir->semester->nama_semester ?? null,
                ],
                'tahun_ajaran' => [
                    'id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'name' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'dosen_pembimbing' => [
                    'id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                ],
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir,
                'nilai_akhir' => $tugasAkhir->nilai_akhir,
                'status_aktif' => $tugasAkhir->status_aktif,
                'berkas_naskah' => $naskahBerkas,
            ];

            return ApiResponse::success($data, 'Detail Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function getRegisterTAForAdmin()
    {
        try {
            // Ambil data TugasAkhir dengan status_valid = 0 menggunakan eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])
                ->where('status_valid', 0)
                ->get();

            // Kembalikan data menggunakan resource
            return ApiResponse::success(TugasAkhirResource::collection($tugasAkhir), 'Tugas Akhir dengan status valid = 0 berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateActiveStatusForAdmin(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status_aktif' => 'required|in:0,1',
            ]);

            $tugasAkhir = TugasAkhir::find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Jika ingin mengaktifkan TA, cek apakah sudah ada TA aktif lain untuk mahasiswa ini
            if ($validatedData['status_aktif'] == 1) {
                $mahasiswaId = $tugasAkhir->mahasiswa_id;
                $adaAktifLain = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                    ->where('id', '!=', $tugasAkhir->id)
                    ->where('status_aktif', 1)
                    ->exists();

                if ($adaAktifLain) {
                    return ApiResponse::error('Mahasiswa ini sudah memiliki Tugas Akhir yang aktif. Nonaktifkan Tugas Akhir aktif lain terlebih dahulu.', 422);
                }
            }

            $tugasAkhir->status_aktif = $validatedData['status_aktif'];
            $tugasAkhir->save();

            return ApiResponse::success([
                'id' => $tugasAkhir->id,
                'status_aktif' => $tugasAkhir->status_aktif,
            ], 'Status Tugas Akhir berhasil diubah');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function storeUjianTAForMahasiswa(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'judul_tugas_akhir' => 'required|string|max:255',
                'naskah_ta' => 'required|file|mimes:pdf',
                'krs' => 'required|file|mimes:pdf',
                'persetujuan_pembimbing' => 'required|file|mimes:pdf',
                'transkrip_nilai' => 'required|file|mimes:pdf',
                'sertifikat_kompetensi' => 'required|file|mimes:pdf',
                'toeic' => 'required|file|mimes:pdf',
                'berita_acara_semhas' => 'required|file|mimes:pdf',
                'surat_perpanjangan_studi' => 'nullable|file|mimes:pdf',
            ]);

            $mahasiswaId = Auth::id();
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();
            $statusForm = StatusForm::where('nama_form', 'tugas akhir')->first();

            if ($statusForm->is_open !== 1) {
                return ApiResponse::error('Pendaftaran tugas akhir tidak dibuka saat ini', 400);
            }

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan atau tidak aktif', 404);
            }

            // Simpan judul tugas akhir
            $tugasAkhir->judul_tugas_akhir = $validatedData['judul_tugas_akhir'];
            $tugasAkhir->status_valid = 0; // Set status_valid ke 0 (menunggu validasi)
            $tugasAkhir->save();

            // Daftar nama berkas dan field request
            $berkasMap = [
                'Naskah Tugas Akhir' => 'naskah_ta',
                'Scan KRS Semester Akhir' => 'krs',
                'Bukti Persetujuan Pembimbing' => 'persetujuan_pembimbing',
                'Transkrip Nilai Mahasiswa' => 'transkrip_nilai',
                'Bukti Sertifikat Kompetensi' => 'sertifikat_kompetensi',
                'Sertifikat TOEIC' => 'toeic',
                'Berita Acara Seminar Hasil' => 'berita_acara_semhas',
                'Surat Perpanjangan Masa Studi' => 'surat_perpanjangan_studi',
            ];

            foreach ($berkasMap as $namaBerkas => $field) {
                if ($request->hasFile($field)) {
                    $originalName = $request->file($field)->getClientOriginalName();
                    $privatePath = "private/tugas_akhir/{$tugasAkhir->id}/{$originalName}";

                    // Simpan file ke storage
                    $filePath = $request->file($field)->storeAs("private/tugas_akhir/{$tugasAkhir->id}", $originalName);

                    // Catat ke DB
                    $tugasAkhir->berkas()->create([
                        'nama_berkas' => $namaBerkas,
                        'file_path' => $privatePath,
                        'status_valid' => 0,
                    ]);
                }
            }

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir->load('berkas')), 'Pendaftaran Ujian TA berhasil, menunggu validasi');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function updateUjianTAForMahasiswa(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'judul_tugas_akhir' => 'required|string|max:255',
                'naskah_ta' => 'nullable|file|mimes:pdf',
                'krs' => 'nullable|file|mimes:pdf',
                'persetujuan_pembimbing' => 'nullable|file|mimes:pdf',
                'transkrip_nilai' => 'nullable|file|mimes:pdf',
                'sertifikat_kompetensi' => 'nullable|file|mimes:pdf',
                'toeic' => 'nullable|file|mimes:pdf',
                'berita_acara_semhas' => 'nullable|file|mimes:pdf',
                'surat_perpanjangan_studi' => 'nullable|file|mimes:pdf',
            ]);

            $mahasiswaId = Auth::id();
            $tugasAkhir = TugasAkhir::with(['berkas'])
                ->where('id', $id)
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan atau tidak aktif', 404);
            }

            // Update judul tugas akhir
            $tugasAkhir->judul_tugas_akhir = $validatedData['judul_tugas_akhir'];
            $tugasAkhir->status_valid = 0; // Reset validasi
            $tugasAkhir->save();

            // Map nama_berkas => field request
            $berkasMap = [
                'Naskah Tugas Akhir' => 'naskah_ta',
                'Scan KRS Semester Akhir' => 'krs',
                'Bukti Persetujuan Pembimbing' => 'persetujuan_pembimbing',
                'Transkrip Nilai Mahasiswa' => 'transkrip_nilai',
                'Bukti Sertifikat Kompetensi' => 'sertifikat_kompetensi',
                'Sertifikat TOEIC' => 'toeic',
                'Berita Acara Seminar Hasil' => 'berita_acara_semhas',
                'Surat Perpanjangan Masa Studi' => 'surat_perpanjangan_studi',
            ];

            foreach ($berkasMap as $namaBerkas => $field) {
                if ($request->hasFile($field)) {
                    $originalName = $request->file($field)->getClientOriginalName();
                    $privatePath = "private/tugas_akhir/{$tugasAkhir->id}/{$originalName}";

                    // Simpan file ke storage (selalu upload file baru)
                    $filePath = $request->file($field)->storeAs("private/tugas_akhir/{$tugasAkhir->id}", $originalName);

                    $berkas = $tugasAkhir->berkas()->where('nama_berkas', $namaBerkas)->first();
                    if ($berkas) {
                        // Hapus file lama jika ada dan berbeda
                        if ($berkas->file_path && $berkas->file_path !== $privatePath && Storage::exists($berkas->file_path)) {
                            Storage::delete($berkas->file_path);
                        }
                        $berkas->file_path = $privatePath;
                        $berkas->status_valid = 0;
                        $berkas->save();
                    } else {
                        $tugasAkhir->berkas()->create([
                            'nama_berkas' => $namaBerkas,
                            'file_path' => $privatePath,
                            'status_valid' => 0,
                        ]);
                    }
                }
            }

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir->load('berkas')), 'Berkas Ujian TA berhasil diupdate');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi Gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi Error: ' . $e->getMessage(), 500);
        }
    }

    public function tugasAkhirAktifByMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();

            // Ambil data TugasAkhir berdasarkan mahasiswa_id
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1) // Hanya ambil yang aktif
                ->first();

            // Jika tidak ditemukan, kembalikan null
            if (!$tugasAkhir) {
                return ApiResponse::success([
                    'tugas_akhir' => null
                ], 'Belum ada Tugas Akhir yang aktif');
            }

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    // Endpoint untuk mengecek apakah mahasiswa sudah memiliki dosen pembimbing
    public function cekDosenPembimbingByMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();

            // Ambil data TugasAkhir berdasarkan mahasiswa_id
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Cek apakah dosen pembimbing sudah ada
            $hasDosenPembimbing = !is_null($tugasAkhir->dosen_pembimbing_id);

            return ApiResponse::success([
                'has_dospem' => $hasDosenPembimbing,
            ], $hasDosenPembimbing ? 'Mahasiswa sudah memiliki dosen pembimbing' : 'Mahasiswa belum memiliki dosen pembimbing');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // ========================== FITUR DASHBOARD ==========================

    public function setDashboardByMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();
            $tugasAkhir = TugasAkhir::with(['proposal', 'bimbingan', 'ujianTA', 'requestPembimbing', 'dosenPenguji'])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();
            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Belum ada Tugas Akhir yang aktif');
            }

            // Ambil bab terbesar yang sudah valid (status_verifikasi = 1)
            $babTerbesar = $tugasAkhir->bimbingan
                ->where('status_verifikasi', 1)
                ->max('bab');

            // Ambil ujian TA terbaru (berdasarkan tanggal_ujian paling akhir)
            $ujianTA = $tugasAkhir->ujianTA
                ->where('status_ujian', 'aktif')
                ->first();

            // Cek apakah sudah ada dosen penguji
            $hasDosenPenguji = $tugasAkhir->dosenPenguji && $tugasAkhir->dosenPenguji->count() > 0;

            $data = [
                'dosen_pembimbing' => $tugasAkhir->dosenPembimbing->name ?? null,
                'proposal' => [
                    'id' => $tugasAkhir->proposal ? $tugasAkhir->id : null,
                    'status' => $tugasAkhir->proposal ? $tugasAkhir->proposal->status_proposal : null,
                    'catatan' => $tugasAkhir->proposal ? $tugasAkhir->catatan_proposal : null,
                ],
                'status_valid' => $tugasAkhir->status_valid,
                'nilai_akhir' => $tugasAkhir->nilai_akhir ? $tugasAkhir->nilai_akhir : null,
                'bimbingan' => [
                    'total_bimbingan' => $tugasAkhir->bimbingan->where('status_verifikasi', 1)->count() ?? null,
                    'progress_bimbingan' => $babTerbesar ?? null,
                ],
                'tanggal_ujian' => $ujianTA ? $ujianTA->tanggal_ujian : null,
                'request_pembimbing' => [
                    'id' => $tugasAkhir->requestPembimbing ? $tugasAkhir->requestPembimbing->id : null,
                    'status' => $tugasAkhir->requestPembimbing ? $tugasAkhir->requestPembimbing->status_request : null
                ],
                'has_dosen_penguji' => $hasDosenPenguji,
                // Tambahkan status_valid pada tugas akhir (jika ingin nested)
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'status_valid' => $tugasAkhir->status_valid,
                ],
            ];

            return ApiResponse::success($data, 'Data keperluan dashboard berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function setDashboardByDosen()
    {
        try {
            $dosenId = Auth::id();

            $tugasAkhir = TugasAkhir::with([
                'bimbingan',
                'ujianTA',
                'requestPembimbing',
                'mahasiswa:id,nim,name',
                'kategori:id,nama_kategori',
            ])
                ->where('status_aktif', 1)
                ->where(function ($q) use ($dosenId) {
                    $q->where('dosen_pembimbing_id', $dosenId)
                        ->orWhereHas('dosenPenguji', function ($q2) use ($dosenId) {
                            $q2->where('dosen_id', $dosenId);
                        });
                })
                ->get();

            $taYangDibimbing = $tugasAkhir->where('dosen_pembimbing_id', $dosenId);

            $requestPembimbing = RequestPembimbing::with(['tugasAkhir.mahasiswa', 'tugasAkhir.kategori'])
                ->where('status_request', 0)
                ->where('dosen_id', $dosenId)
                ->orderBy('tanggal_request', 'asc')
                ->limit(5)
                ->get();

            $tablePermintaanBimbingan = $requestPembimbing->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tugas_akhir_id' => $item->tugas_akhir_id,
                    'dosen_id' => $item->dosen_id,
                    'status_request' => $item->status_request,
                    'nama' => $item->tugasAkhir->mahasiswa->name ?? null,
                    'nim' => $item->tugasAkhir->mahasiswa->nim ?? null,
                    'kategori' => $item->tugasAkhir->kategori->nama_kategori ?? null,
                ];
            });

            $taPerBab = $taYangDibimbing->map(function ($item) {
                return [
                    'id_ta' => $item->id,
                    'bab_tertinggi' => $item->bimbingan
                        ->where('status_verifikasi', 1)
                        ->max('bab')
                ];
            });

            $statistikBab = collect(range(1, 5))->map(function ($bab) use ($taPerBab) {
                $taDiBab = $taPerBab->where('bab_tertinggi', $bab);
                return [
                    'bab' => $bab,
                    'jumlah_ta' => $taDiBab->count(),
                    'daftar_ta' => $taDiBab->values()->all()
                ];
            });

            $ujianHariIni = $tugasAkhir->flatMap(function ($ta) {
                return $ta->ujianTA
                    ->where('tanggal_ujian', now()->format('Y-m-d'))
                    ->where('status_ujian', 'aktif')
                    ->map(function ($ujian) use ($ta) {
                        return [
                            'ujian_ta_id' => $ujian->id,
                            'tugas_akhir_id' => $ta->id,
                            'nim' => $ta->mahasiswa->nim,
                            'tanggal_ujian' => $ujian->tanggal_ujian,
                            'nama' => $ta->mahasiswa->name,
                            'waktu' => date('H:i', strtotime($ujian->sesi->waktu_mulai)) . ' - ' . date('H:i', strtotime($ujian->sesi->waktu_selesai)),
                            'sesi' => $ujian->sesi->nama_sesi ?? null,
                            'ruang' => $ujian->ruang->nama_ruang ?? null
                        ];
                    });
            });

            $ujianTerjadwal = $tugasAkhir->sum(function ($ta) {
                return $ta->ujianTA
                    // ->where('status_ujian', 'aktif')
                    ->count();
            });

            $jumlahReqPembimbing = $requestPembimbing
                ->where('status_request', 0)
                ->where('dosen_id', $dosenId)
                ->count();

            $data = [
                'doughnat_chart' => $statistikBab->pluck('jumlah_ta', 'bab') ?? null,
                'summary_card' => [
                    'ta_yang_dibimbing' => $taYangDibimbing->count(),
                    'ujian_terjadwal' => $ujianTerjadwal ?? null,
                    'permintaan_bimbingan' => $jumlahReqPembimbing ?? null,
                ],
                'ujian_hari_ini' => $ujianHariIni ?? null,
                'permintaan_bimbingan' => $tablePermintaanBimbingan->values() ?? null,
            ];

            return ApiResponse::success($data, 'Data tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DASHBOARD ==========================


    // ========================== FITUR ADMIN ==========================

    // Fitur Bimbingan (Plotting List Dosen Pembimbing & Detail Plotting Pembimbing & Update Plotting Pembimbing)

    public function tabelPlottingPembimbingForAdmin(Request $request)
    {
        try {
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'kategori:id,nama_kategori',
                'requestPembimbing',
                'tahunAjaran:id,tahun_ajaran'
            ]);
            // ->where('status_aktif', 1);

            // Fitur pencarian berdasarkan nama mahasiswa atau NIM
            if ($request->search) {
                $query->whereHas('mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                });
            }

            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }

            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);

            // Fitur filter berdasarkan kategori
            if ($request->kategori) {
                $kategoriList = explode(',', $request->kategori);
                $kategoriList = array_map('trim', $kategoriList);
                $query->whereIn('kategori_id', $kategoriList);
            }

            // Urutkan: status_request = 2 (ditolak) paling atas, lalu id desc
            $query->orderByRaw("CASE WHEN dosen_pembimbing_id IS NULL AND ( SELECT status_request FROM request_pembimbing WHERE tugas_akhir_id = tugas_akhir.id LIMIT 1 ) = 2 THEN 0 WHEN dosen_pembimbing_id IS NOT NULL THEN 2 ELSE 1 END")->orderByDesc('id');

            $tugasAkhir = $query->paginate(10);

            $notificationCount = TugasAkhir::with('requestPembimbing')
                ->where('status_aktif', 1)
                ->whereNull('dosen_pembimbing_id')
                ->whereHas('requestPembimbing', function ($q) {
                    $q->where('status_request', '!=', 1)
                        ->whereIn('status_request', [0, 2]); // 0 = menunggu, 2 = ditolak
                })
                ->count();

            $data = collect($tugasAkhir->items())->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'tahun_ajaran' => [
                        'tahun_ajaran_id' => $item->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing->id ?? null,
                        'name' => $item->dosenPembimbing->name ?? null,
                    ],
                    'kategori' => [
                        'kategori_id' => $item->kategori->id ?? null,
                        'nama_kategori' => $item->kategori->nama_kategori ?? null,
                    ],
                    'request_pembimbing' => $item->requestPembimbing->status_request ?? null,
                ];
            });

            $result = [
                'notification' => $notificationCount,
                'tahun_ajaran_list' => $tahunAjaranList,
                'data' => $data,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data list plotting berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function plottingPembimbingForAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'kategori:id,nama_kategori',
                'tahunAjaran:id,tahun_ajaran',
                'dosenPembimbing:id,name',
                'dosenPenguji:id' // tambahkan eager load dosenPenguji
            ])
                // ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            $daftarDosen = Dosen::select('name', 'nik', 'id', 'akses_ta')
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get();

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => $tugasAkhir->tahunAjaran ? $tugasAkhir->tahunAjaran->tahun_ajaran : null,
                'dosen_pembimbing' => [
                    'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                ],
                // Tambahkan flag sudah ada dosen penguji
                'has_dosen_penguji' => $tugasAkhir->dosenPenguji && $tugasAkhir->dosenPenguji->count() > 0,
            ];
            ;

            return ApiResponse::success([
                [
                    'mahasiswa' => $data,
                    'daftar_dosen' => $daftarDosen
                ]
            ], 'Data Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function storePlottingPembimbingByAdmin(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tugas_akhir_id' => 'required|integer|exists:tugas_akhir,id',
                'dosen_pembimbing_id' => 'required|integer',
            ]);

            // Cek apakah tugas akhir ada
            $tugasAkhir = TugasAkhir::find($validatedData['tugas_akhir_id']);
            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas akhir tidak ditemukan', 404);
            }

            // Cek dosen akses TA
            $dosen = Dosen::where('id', $validatedData['dosen_pembimbing_id'])
                ->where('akses_ta', 1)
                ->where('status', 1)
                ->first();

            if (!$dosen) {
                return ApiResponse::error('Dosen tidak ditemukan atau tidak memiliki akses Tugas Akhir', 404);
            }

            // Update dosen pembimbing di tugas akhir
            $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_pembimbing_id'];
            $tugasAkhir->save();

            // Tolak semua request pembimbing yang statusnya masih 0 (menunggu) untuk tugas akhir ini
            RequestPembimbing::where('tugas_akhir_id', $tugasAkhir->id)
                ->where('status_request', 0)
                ->update([
                    'status_request' => 2, // 2 = ditolak
                    'tanggal_respon' => now(),
                ]);

            // Return tugas akhir dengan relasi dosen pembimbing
            $tugasAkhir->load('dosenPembimbing');

            return ApiResponse::success([
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'dosen_pembimbing' => [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'name' => $tugasAkhir->dosenPembimbing->name,
                    ]
                ]
            ], 'Dosen pembimbing berhasil ditetapkan', 200);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function putPlottingPembimbingByAdmin(Request $request, $tugasAkhirId)
    {
        try {
            $validatedData = $request->validate([
                'dosen_pembimbing_id' => [
                    'required',
                    'exists:dosen,id',
                    function ($attribute, $value, $fail) {
                        $dosen = Dosen::where('id', $value)
                            ->where('akses_ta', 1)
                            ->where('status', 1)
                            ->first();

                        if (!$dosen) {
                            $fail('Dosen yang dipilih tidak memiliki akses Tugas Akhir atau tidak aktif.');
                        }
                    }
                ],
            ]);

            // Cek apakah Tugas Akhir dengan ID tersebut ada
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($tugasAkhirId);

            // CEK: Jika sudah ada dosen penguji, tidak boleh update dosen pembimbing
            $dosenPembimbingId = $tugasAkhir->dosen_pembimbing_id;
            $pengujiId = $tugasAkhir->dosenPenguji->pluck('id')->toArray();

            if ($dosenPembimbingId && in_array($dosenPembimbingId, $pengujiId)) {
                return ApiResponse::error('Tidak dapat mengubah dosen pembimbing karena dosen pembimbing saat ini sudah menjadi dosen penguji.', 400);
            }

            // Update dosen pembimbing
            $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_pembimbing_id'];
            $tugasAkhir->save();

            // Tolak semua request pembimbing yang statusnya masih 0 (menunggu) untuk tugas akhir ini
            RequestPembimbing::where('tugas_akhir_id', $tugasAkhir->id)
                ->where('status_request', 0)
                ->update([
                    'status_request' => 2, // 2 = ditolak
                    'tanggal_respon' => now(),
                ]);

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Dosen pembimbing berhasil diupdate');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Bimbingan (List Bimbingan & Detail Bimbingan)
    public function tabelListBimbinganForAdmin(Request $request)
    {
        try {
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name,nik',
                'bimbingan',
                'kategori:id,nama_kategori',
            ])
                // ->where('status_aktif', 1)
                ->whereNotNull('dosen_pembimbing_id'); // hanya yang sudah punya dosen pembimbing

            if ($request->search) {
                $query->whereHas('mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                });
            }

            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }
            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);
            // Fitur filter berdasarkan kategori
            if ($request->kategori) {
                $kategoriList = explode(',', $request->kategori);
                $kategoriList = array_map('trim', $kategoriList);
                $query->whereIn('kategori_id', $kategoriList);
            }

            $tugasAkhir = $query->orderBy('id', 'desc')->paginate(10);

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'tahun_ajaran' => [
                        'tahun_ajaran_id' => $item->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing->id ?? null,
                        'name' => $item->dosenPembimbing->name ?? null,
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
                'tahun_ajaran_list' => $tahunAjaranList,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data list bimbingan semua mahasiswa berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function detailBimbinganForAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name,nik',
                'kategori:id,nama_kategori',
                'nilaiBimbingan',
                'tahunAjaran:id,tahun_ajaran',
            ])
                // ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            Carbon::setLocale('id');

            // Ambil hanya kolom yang diminta dari tabel bimbingan
            $bimbinganQuery = Bimbingan::where('tugas_akhir_id', $tugasAkhir->id)
                ->orderBy('id', 'desc')
                ->paginate(10, ['id', 'tanggal_bimbingan', 'bab', 'catatan', 'status_verifikasi']);

            $bimbinganData = collect($bimbinganQuery->items())->map(function ($bimbingan) {
                return [
                    'id' => $bimbingan->id,
                    'tanggal_bimbingan' => $bimbingan->tanggal_bimbingan
                        ? Carbon::parse($bimbingan->tanggal_bimbingan)->translatedFormat('l, d F Y')
                        : null,
                    'bab' => $bimbingan->bab,
                    'catatan' => $bimbingan->catatan,
                    'status_verifikasi' => $bimbingan->status_verifikasi,
                ];
            });

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'dosen_pembimbing' => [
                    'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                    'nik' => $tugasAkhir->dosenPembimbing->nik ?? null,
                ],
                'bab_tertinggi' => $tugasAkhir->bimbingan
                    ->where('status_verifikasi', 1)
                    ->max('bab') ?? null,
                'bimbingan' => $bimbinganData,
                'pagination' => [
                    'current_page' => $bimbinganQuery->currentPage(),
                    'last_page' => $bimbinganQuery->lastPage(),
                    'per_page' => $bimbinganQuery->perPage(),
                    'total' => $bimbinganQuery->total(),
                ],
                'nilai_bimbingan' => [
                    'nilai_bimbingan_id' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->id : null,
                    'rerata_bimbingan' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->rerata_bimbingan : null,
                    'disiplin' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->disiplin : null,
                    'komunikasi' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->komunikasi : null,
                    'skill_pengetahuan' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->skill_pengetahuan : null,
                    'tingkah_laku' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->tingkah_laku : null,
                    'catatan' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->catatan_akhir_bimbingan : null,
                ],
            ];
            return ApiResponse::success($data, 'Data detail bimbingan berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Ujian TA (List Plotting Penguji & Detail Plotting Penguji)

    public function tabelPlottingPengujiForAdmin(Request $request)
    {
        try {
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'proposal:id,judul_proposal',
                'bimbingan'
            ])
                // ->where('status_aktif', 1)
                ->withCount([
                    'bimbingan as bimbingan_count' => function ($query) {
                        $query->where('status_verifikasi', 1);
                    }
                ])
                ->having('bimbingan_count', '>=', 9);

            // Search nim, nama, judul proposal
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('mahasiswa', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhereHas('proposal', function ($q2) use ($request) {
                            $q2->where('judul_proposal', 'like', '%' . $request->search . '%');
                        });
                });
            }

            // Filter status plotting penguji
            if ($request->has('status')) {
                $statusList = explode(',', $request->status);
                $query->where(function ($q) use ($statusList) {
                    foreach ($statusList as $status) {
                        $status = (int) trim($status);
                        if ($status === 0) {
                            $q->orWhereDoesntHave('dosenPenguji');
                        } elseif ($status === 1) {
                            $q->orWhereHas('dosenPenguji');
                        }
                    }
                });
            }
            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }

            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);

            // Query Anomali
            $query->orderByRaw("CASE WHEN NOT EXISTS ( SELECT 1 FROM dosen_penguji WHERE dosen_penguji.tugas_akhir_id = tugas_akhir.id ) THEN 0 ELSE 1 END")
                ->orderByDesc('id');

            $tugasAkhir = $query->paginate(10);

            // ✅ PERBAIKAN: Hitung notification count
            $notificationCount = TugasAkhir::where('status_aktif', 1)
                ->withCount([
                    'bimbingan as bimbingan_count' => function ($query) {
                        $query->where('status_verifikasi', 1);
                    }
                ])
                ->having('bimbingan_count', '>=', 9)
                ->whereDoesntHave('dosenPenguji')
                ->count();

            // ✅ PERBAIKAN: Map data dengan pengecekan status lulus per item
            $data = $tugasAkhir->map(function ($item) {
                // ✅ PERBAIKAN: Cek status lulus untuk setiap item individually
                $sudahLulus = in_array($item->status_kelulusan, [1, 2, 4]);

                return [
                    'tugas_akhir_id' => $item->id,
                    'tahun_ajaran' => [
                        'tahun_ajaran_id' => $item->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'sudah_lulus' => $sudahLulus,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'proposal' => [
                        'proposal_id' => $item->proposal->id ?? null,
                        'judul_proposal' => $item->proposal->judul_proposal ?? null,
                    ],
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'dosen_penguji' => $item->dosenPenguji
                        ? $item->dosenPenguji->map(function ($penguji) {
                            return [
                                'dosen_penguji_id' => $penguji->id,
                                'name' => $penguji->name,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
                ];
            });

            $result = [
                'notification' => $notificationCount,
                'tahun_ajaran_list' => $tahunAjaranList,
                'data' => $data,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success(
                $result,
                'List Tugas Akhir dengan minimal 9 bimbingan disetujui berhasil diambil'
            );

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function plottingPengujiForAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name,nik',
                'proposal:id,judul_proposal',
                'tahunAjaran:id,tahun_ajaran',
                'kategori:id,nama_kategori',
                'dosenPenguji:id,name,nik' // tambahkan relasi dosenPenguji
            ])
                // ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();


            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan.', 404);
            }

            // Cek jika mahasiswa sudah lulus atau harus mengulang
            if (in_array($tugasAkhir->status_kelulusan, [1, 2, 4])) {
                return ApiResponse::error('Tidak dapat mengubah penguji karena mahasiswa sudah dinyatakan lulus atau harus mengulang TA.', 403);
            }

            $daftarDosen = Dosen::select('id', 'name', 'nik', 'akses_ta')
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get();

            // Ambil dosen penguji, jika ada
            $dosenPenguji = $tugasAkhir && $tugasAkhir->dosenPenguji
                ? $tugasAkhir->dosenPenguji->map(function ($penguji) {
                    return [
                        'dosenPenguji_id' => $penguji->id,
                        'name' => $penguji->name,
                        'nik' => $penguji->nik,
                        'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                    ];
                })->values()
                : [];

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id ?? null,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'judul_proposal' => $tugasAkhir->proposal->judul_proposal ?? null,
                'dosen_pembimbing' => [
                    'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                    'nik' => $tugasAkhir->dosenPembimbing->nik ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'dosen_penguji' => $dosenPenguji,
                'daftar_dosen' => $daftarDosen
            ];

            return ApiResponse::success($data, 'Data untuk plotting penguji berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Ujian TA (List Pendaftar Ujian & Detail Pendaftar Ujian)

    public function tabelPendaftarUjianForAdmin(Request $request)
    {
        try {
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'berkas'
            ])
                // ->where('status_aktif', 1)
                ->whereIn('status_valid', [0, 1, 2]); // Hanya ambil yang belum validasi atau ditolak

            // Fitur search nim, nama, judul tugas akhir
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('mahasiswa', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $request->search . '%');
                });
            }

            // Fitur filter status (0,1,2 saja, null gadiajak)
            if ($request->has('status')) {
                $statusValidList = array_filter(
                    explode(',', $request->status),
                    fn($v) => in_array((int) $v, [0, 1, 2], true)
                );
                if (count($statusValidList)) {
                    $query->whereIn('status_valid', $statusValidList);
                }
            }

            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }

            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);

            $query->orderByRaw("CASE WHEN status_valid = 0 THEN 0 ELSE 1 END")->orderByDesc('id');

            $tugasAkhir = $query->paginate(10);

            $statusForm = StatusForm::where('nama_form', 'Tugas Akhir')
                ->select('id', 'is_open')
                ->first();

            $notificationCount = TugasAkhir::where('status_aktif', 1)
                ->where('status_valid', 0)
                ->count();

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'tahun_ajaran' => [
                        'tahun_ajaran_id' => $item->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing->id ?? null,
                        'name' => $item->dosenPembimbing->name ?? null,
                    ],
                    'catatan_perbaikan' => $item->catatan_perbaikan ?? null,
                    'status_valid' => $item->status_valid ?? null,
                    'berkas' => $item->berkas ? $item->berkas->map(function ($berkas) {
                        return [
                            'id' => $berkas->id,
                            'nama_berkas' => $berkas->nama_berkas,
                            'file_path' => $berkas->file_path,
                            'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                            'status_valid' => $berkas->status_valid,
                        ];
                    })->values() : [],
                ];
            });

            $result = [
                'notification' => $notificationCount,
                'tahun_ajaran_list' => $tahunAjaranList,
                'status_form' => $statusForm,
                'data' => $data,
                'pagination' => [
                    'current_page' => $tugasAkhir->currentPage(),
                    'last_page' => $tugasAkhir->lastPage(),
                    'per_page' => $tugasAkhir->perPage(),
                    'total' => $tugasAkhir->total(),
                ],
            ];

            return ApiResponse::success($result, 'List Tugas Akhir yang mendaftar ujian berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function showPendaftarUjianForAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'kategori:id,nama_kategori',
                'tahunAjaran:id,tahun_ajaran',
                'berkas'
            ])
                // ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Cek jika mahasiswa sudah lulus
            if (in_array($tugasAkhir->status_kelulusan, [1, 2, 4])) {
                return ApiResponse::error('Tidak dapat mengubah validasi karena mahasiswa sudah dinyatakan lulus/tidak lulus.', 403);
            }

            if ($tugasAkhir->status_valid != 0) {
                return ApiResponse::error('Berkas pendaftaran telah divalidasi', 403);
            }

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir ?? null,
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'catatan_perbaikan' => $tugasAkhir->catatan_perbaikan ?? null,
                'status_valid' => $tugasAkhir->status_valid,
                'berkas' => $tugasAkhir->berkas ? $tugasAkhir->berkas->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => route('berkas.download', ['berkas' => $berkas->id]),
                        'status_valid' => $berkas->status_valid,
                    ];
                })->values() : [],
            ];
            return ApiResponse::success($data, 'Detail Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function validasiBerkasByAdmin(Request $request, $tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with(
                ['berkas']
            )->
                findOrFail($tugasAkhirId);

            // Ambil semua nama berkas yang ada di DB untuk tugas akhir ini
            $berkasList = $tugasAkhir->berkas->pluck('nama_berkas')->toArray();

            // Daftar nama berkas wajib
            $berkasWajib = [
                'Naskah Tugas Akhir',
                'Scan KRS Semester Akhir',
                'Bukti Persetujuan Pembimbing',
                'Transkrip Nilai Mahasiswa',
                'Bukti Sertifikat Kompetensi',
                'Sertifikat TOEIC',
                'Berita Acara Seminar Hasil',
            ];
            $berkasOpsional = 'Surat Perpanjangan Masa Studi';

            // Hitung jumlah berkas yang harus divalidasi
            $jumlahWajib = count($berkasWajib);
            $adaOpsional = in_array($berkasOpsional, $berkasList);
            $jumlahBerkas = $jumlahWajib + ($adaOpsional ? 1 : 0);

            // Validasi array status_valid_berkas harus sesuai jumlah berkas yang ada
            $validatedData = $request->validate([
                'status_valid_berkas' => "required|array|size:$jumlahBerkas",
                'status_valid_berkas.*' => 'required|in:1,2',
                'catatan_perbaikan' => 'nullable|string',
            ]);

            // Update status_valid pada masing-masing berkas sesuai urutan di DB
            foreach ($tugasAkhir->berkas as $i => $berkas) {
                if ($i < $jumlahBerkas) {
                    $berkas->status_valid = $validatedData['status_valid_berkas'][$i];
                    $berkas->save();
                }
            }

            // Cek status_valid tugas akhir berdasarkan status_valid semua berkas yang divalidasi
            $statusList = array_slice($validatedData['status_valid_berkas'], 0, $jumlahBerkas);
            if (in_array(2, $statusList)) {
                $tugasAkhir->status_valid = 2; // Ditolak
            } elseif (count(array_unique($statusList)) === 1 && $statusList[0] == 1) {
                $tugasAkhir->status_valid = 1; // Diterima
            }

            // Tambahkan update catatan_perbaikan jika ada
            if (array_key_exists('catatan_perbaikan', $validatedData)) {
                $tugasAkhir->catatan_perbaikan = $validatedData['catatan_perbaikan'];
            }

            $tugasAkhir->save();

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir->load('berkas')), 'Status tugas akhir & berkas berhasil diperbarui');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Ujian TA (List Jadwal Ujian & Berita Acara Ujian)

    public function tabelPenjadwalanUjianByAdmin(Request $request)
    {
        try {
            // --- 1. Data UjianTA yang sudah dijadwalkan ---
            $query = UjianTA::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.dosenPembimbing:id,name',
                'tugasAkhir.dosenPenguji:id,name',
                'tahunAjaran',
                'sesi',
                'ruang',
                'tugasAkhir.tahunAjaran'
            ]);

            // Fitur search nim, nama, judul tugas akhir
            if ($request->search) {
                $query->where(function ($mainQuery) use ($request) {
                    $mainQuery->whereHas('tugasAkhir.mahasiswa', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhereHas('tugasAkhir', function ($q) use ($request) {
                            $q->where('judul_tugas_akhir', 'like', '%' . $request->search . '%');
                        });
                });
            }

            // Filter tahun ajaran multi value
            if ($request->tahun_ajaran_id) {
                $tahunAjaranList = explode(',', $request->tahun_ajaran_id);
                $tahunAjaranList = array_map('trim', $tahunAjaranList);
                $query->whereIn('tahun_ajaran_id', $tahunAjaranList);
            }

            // Filter status kelulusan (mendukung multi-status)
            $statuses = ($request->has('status') && $request->status) ? explode(',', $request->status) : [];

            if (!empty($statuses)) {
                if ($statuses === ['menunggu']) {
                    $query->whereRaw('1=0'); // Jika hanya 'menunggu', kosongkan query UjianTA
                } else {
                    $query->where(function ($q) use ($statuses) {
                        if (in_array('terjadwal', $statuses)) {
                            $q->orWhere(function ($subQ) {
                                $subQ->where('status_ujian', 'aktif')->whereNull('status_kelulusan');
                            });
                        }
                        if (in_array('revisi', $statuses) || in_array('revisi-lulus', $statuses) || in_array('3', $statuses)) {
                            $q->orWhere('status_kelulusan', 3);
                        }
                        $numericStatuses = array_filter($statuses, 'is_numeric');
                        if (!empty($numericStatuses)) {
                            $q->orWhereIn('status_kelulusan', $numericStatuses);
                        }
                    });
                }
            }

            $query->orderByDesc('id');
            $ujianTA = $query->paginate(10);

            // --- 2. Data TugasAkhir yang belum punya UjianTA aktif ---
            $queryTaBelumUjian = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'tahunAjaran'
            ])
                ->where('status_aktif', 1)
                ->where('status_valid', 1)
                ->where(function ($q) {
                    $q->whereDoesntHave('ujianTA')
                        ->orWhereDoesntHave('ujianTA', function ($q2) {
                            $q2->where('status_ujian', 'aktif');
                        });
                });

            // Fitur search nim, nama, judul tugas akhir
            if ($request->search) {
                $queryTaBelumUjian->where(function ($q) use ($request) {
                    $q->whereHas('mahasiswa', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $request->search . '%');
                });
            }

            // Filter tahun ajaran pada tugas akhir
            if ($request->has('tahun_ajaran_id') && $request->tahun_ajaran_id) {
                $queryTaBelumUjian->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            // Hanya jalankan query ini jika 'menunggu' dipilih atau tidak ada filter status
            if (!empty($statuses) && !in_array('menunggu', $statuses)) {
                $queryTaBelumUjian->whereRaw('1=0');
            }

            $taBelumUjian = $queryTaBelumUjian->get();

            // --- 3. Mapping data ---
            $dataUjianTA = collect($ujianTA->items())->map(function ($item) {
                $ta = $item->tugasAkhir;
                Carbon::setLocale('id');
                $status_kelulusan = $item->status_kelulusan;
                if (is_null($status_kelulusan)) {
                    $status_kategori = $item->status_ujian === 'aktif' ? 'terjadwal' : 'menunggu';
                } else {
                    switch ($status_kelulusan) {
                        case 1:
                            $status_kategori = 'lulus_tanpa_revisi';
                            break;
                        case 2:
                            $status_kategori = 'lulus_revisi_tanpa_diujikan';
                            break;
                        case 3:
                            $status_kategori = 'belum_lulus_revisi_diujikan_lagi';
                            break;
                        case 4:
                            $status_kategori = 'tidak_lulus_ulang_ta';
                            break;
                        default:
                            $status_kategori = 'tidak_diketahui';
                    }
                }

                return [
                    'ujian_ta' => [
                        'ujian_ta_id' => $item->id,
                        'tanggal_ujian' => $item->tanggal_ujian
                            ? Carbon::parse($item->tanggal_ujian)->translatedFormat('l, d F Y')
                            : null,
                        'waktu_mulai' => $item->sesi->waktu_mulai ?? null,
                        'waktu_selesai' => $item->sesi->waktu_selesai ?? null,
                        'ruang' => $item->ruang->nama_ruang ?? null,
                        'status_ujian' => $item->status_ujian ?? null,
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? null,
                        'status_kelulusan' => $item->status_kelulusan ?? null,
                    ],
                    'tugas_akhir_id' => $ta->id ?? null,
                    'berita_acara_url' => url('/api/admin_ta/cetak-berita-acara-ujian/' . ($item->id ?? 0)),
                    'mahasiswa' => [
                        'mahasiswa_id' => $ta->mahasiswa->id ?? null,
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'name' => $ta->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? null,
                    'status_kategori' => $status_kategori,
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $ta->dosenPembimbing->id ?? null,
                        'name' => $ta->dosenPembimbing->name ?? null,
                    ],
                    'nilai_akhir' => $item->nilai_akhir ?? null,
                    'dosen_penguji' => $ta->dosenPenguji
                        ? $ta->dosenPenguji->map(function ($penguji) {
                            return [
                                'dosen_penguji_id' => $penguji->id ?? null,
                                'name' => $penguji->name ?? null,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
                ];
            });

            $dataTaBelumUjian = $taBelumUjian->map(function ($ta) {
                return [
                    'ujian_ta' => null,
                    'tugas_akhir_id' => $ta->id ?? null,
                    'berita_acara_url' => null,
                    'mahasiswa' => [
                        'mahasiswa_id' => $ta->mahasiswa->id ?? null,
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'name' => $ta->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? null,
                    'status_kategori' => 'menunggu',
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $ta->dosenPembimbing->id ?? null,
                        'name' => $ta->dosenPembimbing->name ?? null,
                    ],
                    'dosen_penguji' => $ta->dosenPenguji
                        ? $ta->dosenPenguji->map(function ($penguji) {
                            return [
                                'dosen_penguji_id' => $penguji->id ?? null,
                                'name' => $penguji->name ?? null,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
                ];
            });

            // Gabungkan data, data yang sudah dijadwalkan tetap dipaging, yang belum dijadwalkan ditambahkan di awal
            $dataGabungan = $dataTaBelumUjian->concat($dataUjianTA)->values();
            // Ambil tahun ajaran list (tidak berubah)
            $currentYear = now()->year;
            $tahunAjaranList = TahunAjaran::where(function ($query) use ($currentYear) {
                for ($i = 0; $i < 5; $i++) {
                    $year = $currentYear - $i;
                    if ($i == 0) {
                        $query->where('tahun_ajaran', 'like', '%' . $year . '%');
                    } else {
                        $query->orWhere('tahun_ajaran', 'like', '%' . $year . '%');
                    }
                }
            })
                ->orderByDesc('tahun_ajaran')
                ->get(['id', 'tahun_ajaran']);
            $notification = TugasAkhir::where('status_valid', 1)
                ->where('status_aktif', 1)
                ->where(function ($q) {
                    $q->whereDoesntHave('ujianTA', function ($q2) {
                        $q2->where('status_ujian', 'aktif');
                    })
                        ->whereHas('ujianTA', function ($q3) {
                            // dummy
                        }, '<', 2);
                })
                ->count();
            $result = [
                'notification' => $notification,
                'tahun_ajaran_list' => $tahunAjaranList,
                'data' => $dataGabungan,
                'pagination' => [
                    'current_page' => $ujianTA->currentPage(),
                    'last_page' => $ujianTA->lastPage(),
                    'per_page' => $ujianTA->perPage(),
                    'total' => $ujianTA->total() + $dataTaBelumUjian->count(),
                ],
            ];

            return ApiResponse::success($result, 'List seluruh penjadwalan ujian TA (termasuk histori & yang belum dijadwalkan) berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function showPenjadwalanUjianByAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'kategori:id,nama_kategori',
                'tahunAjaran:id,tahun_ajaran',
                'ujianTA.sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
                'ujianTA.ruang:id,nama_ruang'
            ])
                // ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }
            if (in_array($tugasAkhir->status_kelulusan, [1, 2, 4])) {
                return ApiResponse::error('Tidak dapat mengubah validasi karena tugas akhir sudah dinyatakan lulus/tidak lulus.', 403);
            }

            // Ambil list dosen penguji untuk checking conflicts
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('id')->toArray();

            $ujianTA = $tugasAkhir->ujianTA->where('status_ujian', 'aktif')->first();
            Carbon::setLocale('id');

            // Get initial availability (all available without constraints)
            $availableDates = $this->getAvailableDates($listDosenPenguji);
            $availableRuang = $this->getAvailableRuang(null, null, $listDosenPenguji);
            $availableSesi = $this->getAvailableSesi(null, null, $listDosenPenguji);

            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir ?? null,
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'status_kelulusan' => $tugasAkhir->status_kelulusan,
                'dosen_pembimbing' => [
                    'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                ],
                'ujian_ta' => $ujianTA ? [
                    'ujian_ta_id' => $ujianTA->id,
                    'tanggal_ujian' => $ujianTA->tanggal_ujian,
                    'sesi' => $ujianTA->sesi ? [
                        'sesi_id' => $ujianTA->sesi->id,
                        'nama_sesi' => $ujianTA->sesi->nama_sesi,
                        'waktu_mulai' => $ujianTA->sesi->waktu_mulai,
                        'waktu_selesai' => $ujianTA->sesi->waktu_selesai,
                    ] : null,
                    'ruang' => $ujianTA->ruang ? [
                        'ruang_id' => $ujianTA->ruang->id,
                        'nama_ruang' => $ujianTA->ruang->nama_ruang,
                    ] : null,
                    'status_ujian' => $ujianTA->status_ujian
                ] : null,
                'dosen_penguji' => $tugasAkhir->dosenPenguji
                    ? $tugasAkhir->dosenPenguji->map(function ($penguji) {
                        return [
                            'dosen_penguji_id' => $penguji->id,
                            'name' => $penguji->name,
                            'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                        ];
                    })->values()
                    : [],
                // Dynamic availability data
                'availability' => [
                    'available_dates' => $availableDates,
                    'available_ruang' => $availableRuang,
                    'available_sesi' => $availableSesi,
                ],
            ];

            return ApiResponse::success($data, 'Detail Tugas Akhir untuk penjadwalan ujian berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function checkAvailabilityForScheduling(Request $request, $tugasAkhirId)
    {
        try {
            $request->validate([
                'tanggal_ujian' => 'nullable|date',
                'ruang_id' => 'nullable|exists:ruang,id',
                'sesi_id' => 'nullable|exists:sesi,id',
                'ujian_ta_id' => 'nullable|exists:ujian_ta,id', // ✅ TAMBAHAN: Terima ujian_ta_id
            ]);

            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($tugasAkhirId);
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('id')->toArray();

            $tanggal = $request->tanggal_ujian;
            $ruangId = $request->ruang_id;
            $sesiId = $request->sesi_id;
            $ujianTaId = $request->ujian_ta_id; // ✅ TAMBAHAN: Ambil ujian_ta_id dari request

            // 1. Get available dates (next 30 days)
            $availableDates = $this->getAvailableDates($listDosenPenguji, $ruangId, $sesiId, $ujianTaId);

            // 2. Get available ruang based on selected date and sesi
            $availableRuang = $this->getAvailableRuang($tanggal, $sesiId, $listDosenPenguji, $ujianTaId);

            // 3. Get available sesi based on selected date and ruang
            $availableSesi = $this->getAvailableSesi($tanggal, $ruangId, $listDosenPenguji, $ujianTaId);

            return ApiResponse::success([
                'available_dates' => $availableDates,
                'available_ruang' => $availableRuang,
                'available_sesi' => $availableSesi,
            ], 'Data availability berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // ✅ PERBAIKAN: Tambahkan parameter ujianTaId ke semua method availability
    private function getAvailableDates($listDosenPenguji, $ruangId = null, $sesiId = null, $ujianTaId = null)
    {
        $dates = [];
        $startDate = now();
        $endDate = now()->addDays(60);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');

            // Skip weekends (optional)
            if ($date->isWeekend()) {
                continue;
            }

            $isAvailable = true;

            // ✅ PERBAIKAN: Cek conflict ruang + sesi + tanggal (kombinasi lengkap)
            if ($ruangId && $sesiId) {
                $ruangSesiConflict = UjianTA::where('tanggal_ujian', $dateString)
                    ->where('ruang_id', $ruangId)
                    ->where('sesi_id', $sesiId)
                    ->where('status_ujian', 'aktif');

                // ✅ KUNCI: Abaikan ujian yang sedang diedit
                if ($ujianTaId) {
                    $ruangSesiConflict->where('id', '!=', $ujianTaId);
                }

                $ruangSesiConflict = $ruangSesiConflict->exists();

                if ($ruangSesiConflict) {
                    $isAvailable = false;
                }
            }

            // ✅ PERBAIKAN: Cek conflict dosen (tanggal + sesi sama, ruang boleh beda)
            if ($isAvailable && $sesiId && !empty($listDosenPenguji)) {
                $dosenConflict = UjianTA::where('tanggal_ujian', $dateString)
                    ->where('sesi_id', $sesiId)
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($q) use ($listDosenPenguji) {
                        $q->whereIn('dosen_id', $listDosenPenguji);
                    });

                // ✅ KUNCI: Abaikan ujian yang sedang diedit
                if ($ujianTaId) {
                    $dosenConflict->where('id', '!=', $ujianTaId);
                }

                if ($dosenConflict->exists()) {
                    $isAvailable = false;
                }
            }

            if ($isAvailable) {
                $dates[] = [
                    'date' => $dateString,
                    'formatted_date' => $date->locale('id')->translatedFormat('l, d F Y'),
                ];
            }
        }

        return $dates;
    }

    private function getAvailableRuang($tanggal = null, $sesiId = null, $listDosenPenguji = [], $ujianTaId = null)
    {
        $allRuang = Ruang::select('id', 'nama_ruang')->get();
        $availableRuang = [];

        foreach ($allRuang as $ruang) {
            $isAvailable = true;

            // ✅ PERBAIKAN: Cek conflict ruang + sesi + tanggal
            if ($tanggal && $sesiId) {
                $ruangSesiConflict = UjianTA::where('tanggal_ujian', $tanggal)
                    ->where('ruang_id', $ruang->id)
                    ->where('sesi_id', $sesiId)
                    ->where('status_ujian', 'aktif');

                // ✅ KUNCI: Abaikan ujian yang sedang diedit
                if ($ujianTaId) {
                    $ruangSesiConflict->where('id', '!=', $ujianTaId);
                }

                if ($ruangSesiConflict->exists()) {
                    $isAvailable = false;
                }
            }

            // ✅ TAMBAHAN: Cek conflict dosen jika ada tanggal dan sesi
            if ($isAvailable && $tanggal && $sesiId && !empty($listDosenPenguji)) {
                $dosenConflict = UjianTA::where('tanggal_ujian', $tanggal)
                    ->where('sesi_id', $sesiId)
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($q) use ($listDosenPenguji) {
                        $q->whereIn('dosen_id', $listDosenPenguji);
                    });

                // ✅ KUNCI: Abaikan ujian yang sedang diedit
                if ($ujianTaId) {
                    $dosenConflict->where('id', '!=', $ujianTaId);
                }

                if ($dosenConflict->exists()) {
                    $isAvailable = false;
                }
            }

            if ($isAvailable) {
                $availableRuang[] = [
                    'ruang_id' => $ruang->id,
                    'nama_ruang' => $ruang->nama_ruang,
                ];
            }
        }

        return $availableRuang;
    }

    private function getAvailableSesi($tanggal = null, $ruangId = null, $listDosenPenguji = [], $ujianTaId = null)
    {
        $allSesi = Sesi::select('id', 'nama_sesi', 'waktu_mulai', 'waktu_selesai')->get();
        $availableSesi = [];

        foreach ($allSesi as $sesi) {
            $isAvailable = true;

            if ($tanggal) {
                // ✅ PERBAIKAN: Cek conflict ruang + sesi + tanggal
                if ($ruangId) {
                    $ruangSesiConflict = UjianTA::where('tanggal_ujian', $tanggal)
                        ->where('ruang_id', $ruangId)
                        ->where('sesi_id', $sesi->id)
                        ->where('status_ujian', 'aktif');

                    // ✅ KUNCI: Abaikan ujian yang sedang diedit
                    if ($ujianTaId) {
                        $ruangSesiConflict->where('id', '!=', $ujianTaId);
                    }

                    if ($ruangSesiConflict->exists()) {
                        $isAvailable = false;
                    }
                }

                // ✅ PERBAIKAN: Cek conflict dosen (tanggal + sesi sama, ruang boleh beda)
                if ($isAvailable && !empty($listDosenPenguji)) {
                    $dosenConflict = UjianTA::where('tanggal_ujian', $tanggal)
                        ->where('sesi_id', $sesi->id)
                        ->where('status_ujian', 'aktif')
                        ->whereHas('tugasAkhir.dosenPenguji', function ($q) use ($listDosenPenguji) {
                            $q->whereIn('dosen_id', $listDosenPenguji);
                        });

                    // ✅ KUNCI: Abaikan ujian yang sedang diedit
                    if ($ujianTaId) {
                        $dosenConflict->where('id', '!=', $ujianTaId);
                    }

                    if ($dosenConflict->exists()) {
                        $isAvailable = false;
                    }
                }
            }

            if ($isAvailable) {
                $availableSesi[] = [
                    'sesi_id' => $sesi->id,
                    'nama_sesi' => $sesi->nama_sesi,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                ];
            }
        }

        return $availableSesi;
    }
    // ========================== END FITUR ADMIN ==========================


    // ========================== FITUR MAHASISWA ==========================

    public function ujianTAForMahasiswa()
    {
        try {
            $mahasiswaId = Auth::id();

            $tugasAkhir = TugasAkhir::with([
                'dosenPembimbing:id,name',
                'kategori:id,nama_kategori',
                'tahunAjaran:id,tahun_ajaran',
                'berkas',
                'bimbingan',
                'dosenPenguji:id,name' // Tambahkan eager load dosenPenguji
            ])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();

            $statusForm = StatusForm::where('nama_form', 'Tugas Akhir')
                ->select('id', 'is_open')
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Belum ada Tugas Akhir yang aktif');
            }

            $naskahBerkas = $tugasAkhir->berkas
                ->where('nama_berkas', 'Naskah Tugas Akhir')
                ->map(function ($berkas) {
                    return [
                        'id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                    ];
                })
                ->values();

            // Cek apakah sudah ada dosen penguji
            $hasDosenPenguji = $tugasAkhir->dosenPenguji && $tugasAkhir->dosenPenguji->count() > 0;

            $result = [
                'status_form' => [
                    'id' => $statusForm->id,
                    'is_open' => $statusForm->is_open,
                ],
                'tugas_akhir' => [
                    'id' => $tugasAkhir->id,
                    'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir,
                    'dosen_pembimbing' => $tugasAkhir->dosenPembimbing ? [
                        'id' => $tugasAkhir->dosenPembimbing->id,
                        'nama' => $tugasAkhir->dosenPembimbing->name,
                    ] : null,
                    'kategori' => $tugasAkhir->kategori ? [
                        'id' => $tugasAkhir->kategori->id,
                        'nama' => $tugasAkhir->kategori->nama_kategori,
                    ] : null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran ? [
                        'id' => $tugasAkhir->tahunAjaran->id,
                        'nama' => $tugasAkhir->tahunAjaran->tahun_ajaran,
                    ] : null,
                    'status_valid' => $tugasAkhir->status_valid,
                    'has_dosen_penguji' => $hasDosenPenguji,
                ],
                'bimbingan' => [
                    'total_bimbingan' => $tugasAkhir->bimbingan
                        ? $tugasAkhir->bimbingan->where('status_verifikasi', 1)->count()
                        : 0,
                ],
                'berkas' => $naskahBerkas,
            ];

            return ApiResponse::success($result, 'Data tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Gagal mengambil data tugas akhir: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR MAHASISWA ==========================


    // ========================== FITUR DOSEN ==========================

    public function tabelUjianTAForDosen(Request $request)
    {
        try {
            $dosenId = Auth::id();

            $query = UjianTA::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.kategori:id,nama_kategori',
                'sesi',
                'ruang'
            ])
                ->whereHas('tugasAkhir', function ($q) use ($dosenId) {
                    $q->whereHas('dosenPenguji', function ($q2) use ($dosenId) {
                        $q2->where('dosen_id', $dosenId);
                    });
                });

            // Fitur search berdasarkan nama mahasiswa, NIM, atau judul tugas akhir
            if ($request->search) {
                $query->whereHas('tugasAkhir', function ($q) use ($request) {
                    $q->whereHas('mahasiswa', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $request->search . '%');
                });
            }

            // Filter status (0 = menunggu, 1 = selesai)
            if ($request->has('status')) {
                $statusList = array_filter(
                    explode(',', $request->status),
                    fn($v) => in_array((int) $v, [0, 1], true)
                );
                if (count($statusList) === 1) {
                    if ((int) $statusList[0] === 0) {
                        $query->whereNull('status_kelulusan');
                    } elseif ((int) $statusList[0] === 1) {
                        $query->whereNotNull('status_kelulusan');
                    }
                }
            }

            $query->orderByDesc('tanggal_ujian');

            $ujianTA = $query->paginate(10);

            $data = collect($ujianTA->items())->map(function ($item) {
                Carbon::setLocale('id');
                $ta = $item->tugasAkhir;
                return [
                    'ujian_ta_id' => $item->id,
                    'tugas_akhir_id' => $ta->id ?? null,
                    'mahasiswa' => [
                        'mahasiswa_id' => $ta->mahasiswa->id ?? null,
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'name' => $ta->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? null,
                    'status_kelulusan' => $item->status_kelulusan ?? null,
                    'kategori' => [
                        'kategori_id' => $ta->kategori->id ?? null,
                        'nama_kategori' => $ta->kategori->nama_kategori ?? null,
                    ],
                    'tanggal_ujian' => $item->tanggal_ujian
                        ? Carbon::parse($item->tanggal_ujian)->translatedFormat('l, d F Y')
                        : null,
                    'sesi' => $item->sesi ? [
                        'sesi_id' => $item->sesi->id ?? null,
                        'nama_sesi' => $item->sesi->nama_sesi ?? null,
                        'waktu_mulai' => $item->sesi->waktu_mulai ? date('H:i', strtotime($item->sesi->waktu_mulai)) : null,
                        'waktu_selesai' => $item->sesi->waktu_selesai ? date('H:i', strtotime($item->sesi->waktu_selesai)) : null,
                    ] : null,
                    'ruang' => $item->ruang ? $item->ruang->nama_ruang : null,
                ];
            });

            $result = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $ujianTA->currentPage(),
                    'last_page' => $ujianTA->lastPage(),
                    'per_page' => $ujianTA->perPage(),
                    'total' => $ujianTA->total(),
                ],
            ];

            return ApiResponse::success($result, 'List Ujian TA yang diujikan berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function detailUjianForDosen($ujianTaId)
    {
        try {
            $dosenId = Auth::id();

            // Ambil UjianTA beserta relasi tugas akhir dan lain-lain
            $ujianTA = UjianTA::with([
                'tugasAkhir.mahasiswa:id,nim,name',
                'tugasAkhir.dosenPembimbing:id,name,nik',
                'tugasAkhir.dosenPenguji:id,name',
                'tugasAkhir.tahunAjaran:id,tahun_ajaran',
                'tugasAkhir.kategori:id,nama_kategori',
                'tugasAkhir.berkas',
                'sesi',
                'ruang'
            ])->find($ujianTaId);

            if (!$ujianTA || !$ujianTA->tugasAkhir) {
                return ApiResponse::success(null, 'Detail ujian untuk TA ini tidak ditemukan');
            }

            $tugasAkhir = $ujianTA->tugasAkhir;

            // Cek apakah dosen login adalah penguji pada tugas akhir ini
            $cekDosenPenguji = $tugasAkhir->dosenPenguji->contains('id', $dosenId);
            if (!$cekDosenPenguji) {
                return ApiResponse::success(null, 'Anda tidak memiliki akses ke detail ujian ini');
            }

            $naskahBerkas = $tugasAkhir->berkas
                ->where('nama_berkas', 'Naskah Tugas Akhir')
                ->map(function ($berkas) {
                    return [
                        'berkas_id' => $berkas->id,
                        'nama_berkas' => $berkas->nama_berkas,
                        'file_path' => $berkas->file_path,
                        'download_url' => $berkas ? route('berkas.download', ['berkas' => $berkas->id]) : null,
                    ];
                })
                ->values();

            Carbon::setLocale('id');
            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'ujian_ta_id' => $ujianTA->id,
                'status_kelulusan' => $ujianTA->status_kelulusan ?? null,
                'mahasiswa' => [
                    'mahasiswa_id' => $tugasAkhir->mahasiswa->id ?? null,
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'kategori' => [
                    'kategori_id' => $tugasAkhir->kategori->id ?? null,
                    'nama_kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                ],
                'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir ?? null,
                'berkas' => $naskahBerkas,
                'tahun_ajaran' => [
                    'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                    'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                ],
                'dosen_pembimbing' => [
                    'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                    'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                    'nik' => $tugasAkhir->dosenPembimbing->nik ?? null,
                ],
                'ujian_ta' => [
                    'ujian_ta_id' => $ujianTA->id,
                    'tanggal_ujian' => $ujianTA->tanggal_ujian
                        ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                        : null,
                    'waktu_mulai' => $ujianTA->sesi ? date('H:i', strtotime($ujianTA->sesi->waktu_mulai)) : null,
                    'waktu_selesai' => $ujianTA->sesi ? date('H:i', strtotime($ujianTA->sesi->waktu_selesai)) : null,
                    'ruang' => $ujianTA->ruang->nama_ruang ?? null,
                    'status_ujian' => $ujianTA->status_ujian
                ],
                'dosen_penguji' => $tugasAkhir->dosenPenguji
                    ? $tugasAkhir->dosenPenguji->map(function ($penguji) use ($dosenId, $ujianTA) {
                        $isCurrentUser = $penguji->id == $dosenId;
                        // Ambil nilai ujian TA dari tabel nilai_ujian_ta
                        $nilai = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                            ->where('dosen_id', $penguji->id)
                            ->first();
                        return [
                            'dosen_penguji_id' => $penguji->id,
                            'name' => $penguji->name,
                            'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            'is_current_user' => $isCurrentUser,
                            'nilai_ujian_ta' => $nilai ? [
                                'id' => $nilai->id,
                                'kualitas_tata_laporan' => $nilai->kualitas_tata_laporan,
                                'kemampuan_teknis' => $nilai->kemampuan_teknis,
                                'kualitas_produk' => $nilai->kualitas_produk,
                                'presentasi' => $nilai->presentasi,
                                'rerata_ujian_ta' => $nilai->rerata_ujian_ta,
                                'revisi' => $nilai->revisi,
                            ] : null,
                        ];
                    })->values()
                    : [],
            ];

            return ApiResponse::success($data, 'Detail ujian tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function checkUjianRevisionStatus($ujianTaId)
    {
        try {
            $currentUjian = UjianTA::with('tugasAkhir')->findOrFail($ujianTaId);
            $tugasAkhirId = $currentUjian->tugas_akhir_id;

            // Cari UjianTA sebelumnya dengan status_kelulusan = 3 (revisi)
            // Pastikan hanya yang terjadi sebelum ujian ini
            $previousRevisionExam = UjianTA::where('tugas_akhir_id', $tugasAkhirId)
                ->where('id', '!=', $ujianTaId)
                ->where('status_kelulusan', 3)
                ->where(function ($query) use ($currentUjian) {
                    if ($currentUjian->tanggal_ujian) {
                        $query->where('tanggal_ujian', '<', $currentUjian->tanggal_ujian);
                    }
                    // Fallback ke ID jika tanggal sama/tidak ada
                    $query->orWhere('id', '<', $currentUjian->id);
                })
                ->orderByDesc('tanggal_ujian')
                ->orderByDesc('id')
                ->exists();

            $data = [
                'is_retake' => $previousRevisionExam,
                'tugas_akhir_id' => $tugasAkhirId,
                'ujian_ta_id' => $currentUjian->id,
            ];

            return ApiResponse::success(
                $data,
                $previousRevisionExam
                    ? 'Ujian ini adalah ujian ulang setelah revisi'
                    : 'Ujian ini bukan ujian ulang'
            );
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatusKelulusanDanNilaiByDosenPenguji(Request $request, $tugasAkhirId)
    {
        try {
            $request->validate([
                'ujian_ta_id' => 'required|exists:ujian_ta,id',
                'kualitas_tata_laporan' => 'required|numeric|min:0|max:100',
                'kemampuan_teknis' => 'required|numeric|min:0|max:100',
                'kualitas_produk' => 'required|numeric|min:0|max:100',
                'presentasi' => 'required|numeric|min:0|max:100',
                'rerata_ujian_ta' => 'required|numeric|min:0|max:100',
                'revisi' => 'nullable|string',
                'status_kelulusan' => 'nullable|in:1,2,3,4',
            ]);

            $dosenId = Auth::id();
            $tugasAkhir = TugasAkhir::with(['dosenPenguji', 'berkas', 'ujianTA', 'nilaiBimbingan'])->find($tugasAkhirId);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            $dosenPenguji = $tugasAkhir->dosenPenguji->firstWhere('id', $dosenId);
            if (!$dosenPenguji) {
                return ApiResponse::error('Anda bukan dosen penguji pada tugas akhir ini.', 403);
            }

            $ujianTA = $tugasAkhir->ujianTA->firstWhere('id', $request->ujian_ta_id);
            if (!$ujianTA) {
                return ApiResponse::error('Ujian TA tidak ditemukan untuk tugas akhir ini.', 404);
            }

            // Jika sekretaris, cek 2 penguji lain sudah input nilai untuk ujian TA ini
            if (($dosenPenguji->pivot->jabatan_penguji ?? null) === 'sekretaris') {
                $pengujiLain = $tugasAkhir->dosenPenguji
                    ->filter(function ($penguji) use ($dosenId, $ujianTA) {
                        return $penguji->id != $dosenId &&
                            NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                                ->where('dosen_id', $penguji->id)
                                ->exists();
                    })
                    ->count();

                if ($pengujiLain < 2) {
                    return ApiResponse::error('Sekretaris hanya bisa submit setelah 2 dosen penguji lain sudah menginput nilai.', 403);
                }
            }

            // Simpan nilai ujian TA (update jika sudah ada)
            $nilaiUjianTA = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                ->where('dosen_id', $dosenId)
                ->first();

            // Bersihkan input revisi sebelum digunakan
            $cleanedRevisi = $request->filled('revisi') ? Purifier::clean($request->revisi) : null;

            if ($nilaiUjianTA) {
                $nilaiUjianTA->update([
                    'kualitas_tata_laporan' => $request->kualitas_tata_laporan,
                    'kemampuan_teknis' => $request->kemampuan_teknis,
                    'kualitas_produk' => $request->kualitas_produk,
                    'presentasi' => $request->presentasi,
                    'rerata_ujian_ta' => $request->rerata_ujian_ta,
                    'revisi' => $cleanedRevisi,
                ]);
            } else {
                $nilaiUjianTA = NilaiUjianTA::create([
                    'ujian_ta_id' => $ujianTA->id,
                    'dosen_id' => $dosenId,
                    'kualitas_tata_laporan' => $request->kualitas_tata_laporan,
                    'kemampuan_teknis' => $request->kemampuan_teknis,
                    'kualitas_produk' => $request->kualitas_produk,
                    'presentasi' => $request->presentasi,
                    'rerata_ujian_ta' => $request->rerata_ujian_ta,
                    'revisi' => $cleanedRevisi,
                ]);
            }

            // Jika status_kelulusan dikirim, update juga di tugas_akhir
            if ($request->filled('status_kelulusan')) {
                $tugasAkhir->status_kelulusan = $request->status_kelulusan;
                $tugasAkhir->save();
            }

            // Jika sekretaris, update status kelulusan pada semua ujianTA terkait
            if (($dosenPenguji->pivot->jabatan_penguji ?? null) === 'sekretaris' && $request->filled('status_kelulusan')) {
                foreach ($tugasAkhir->ujianTA as $ujian) {
                    if ($ujian->status_ujian === 'aktif') {
                        $ujian->status_kelulusan = $request->status_kelulusan;
                        $ujian->save();
                    }
                }

                // Jika status_kelulusan = 3 (revisi) atau 4 (tidak lulus)
                if (in_array($request->status_kelulusan, [3, 4])) {
                    foreach ($tugasAkhir->berkas as $berkas) {
                        if ($request->status_kelulusan == 3 && $berkas->nama_berkas === 'Naskah Tugas Akhir') {
                            $berkas->status_valid = 3; // 3 = perlu revisi
                            $berkas->save();
                        }
                    }
                    // Hapus/set nilai ke 0 jika revisi, set ke 50 jika tidak lulus
                    $nilaiUjianTAList = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                        ->whereIn('dosen_id', $tugasAkhir->dosenPenguji->pluck('id'))
                        ->get();

                    if ($request->status_kelulusan == 3) {
                        foreach ($nilaiUjianTAList as $nilai) {
                            $nilai->update([
                                'kualitas_tata_laporan' => 0,
                                'kemampuan_teknis' => 0,
                                'kualitas_produk' => 0,
                                'presentasi' => 0,
                                'rerata_ujian_ta' => 0,
                                // revisi tetap dipertahankan
                            ]);
                        }
                    } elseif ($request->status_kelulusan == 4) {
                        foreach ($nilaiUjianTAList as $nilai) {
                            $nilai->update([
                                'kualitas_tata_laporan' => 50,
                                'kemampuan_teknis' => 50,
                                'kualitas_produk' => 50,
                                'presentasi' => 50,
                                'rerata_ujian_ta' => 50,
                                'revisi' => null,
                            ]);
                        }
                    }

                    foreach ($tugasAkhir->ujianTA as $ujian) {
                        if ($ujian->status_ujian === 'aktif') {
                            $ujian->status_ujian = 'non-aktif';
                            $ujian->save();
                        }
                    }
                    if ($request->status_kelulusan == 3) {
                        $tugasAkhir->status_valid = 3; // biar bisa revisi
                    } elseif ($request->status_kelulusan == 4) {
                        $tugasAkhir->status_aktif = 0; // non-aktifkan TA
                    }
                    $tugasAkhir->save();
                }

                // Jika status_kelulusan = 1 (lulus tanpa revisi), hapus semua catatan revisi dosen penguji
                if ($request->status_kelulusan == 1) {
                    $nilaiUjianTAList = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                        ->whereIn('dosen_id', $tugasAkhir->dosenPenguji->pluck('id'))
                        ->get();

                    foreach ($nilaiUjianTAList as $nilai) {
                        $nilai->revisi = null;
                        $nilai->save();
                    }
                }

                // === Hitung nilai akhir jika semua nilai sudah ada, KECUALI status_kelulusan == 3 ===
                if ($request->status_kelulusan != 3) {
                    $nilaiProposal = $tugasAkhir->nilai_proposal;
                    $nilaiBimbingan = $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->rerata_bimbingan : null;
                    $nilaiUjianTACollection = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)
                        ->whereIn('dosen_id', $tugasAkhir->dosenPenguji->pluck('id'))
                        ->pluck('rerata_ujian_ta');

                    if ($nilaiProposal !== null && $nilaiBimbingan !== null && $nilaiUjianTACollection->count() === 3) {
                        $rerataUjianTA = $nilaiUjianTACollection->avg();
                        $nilaiAkhir = round(
                            ($nilaiProposal * 0.1) +
                            ($nilaiBimbingan * 0.3) +
                            ($rerataUjianTA * 0.6),
                            2
                        );
                        $tugasAkhir->update([
                            'nilai_akhir' => $nilaiAkhir,
                        ]);
                        // Update nilai_akhir hanya pada ujianTA terbaru (latest) milik tugas akhir ini
                        $latestUjianTA = $tugasAkhir->ujianTA()->orderByDesc('id')->first();
                        if ($latestUjianTA) {
                            $latestUjianTA->nilai_akhir = $nilaiAkhir;
                            $latestUjianTA->save();
                        }
                    }
                }
                // === End hitung nilai akhir ===
            }

            return ApiResponse::success(
                new TugasAkhirResource($tugasAkhir),
                'Status kelulusan berhasil diubah'
            );
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // ========================== END FITUR DOSEN ==========================
}
