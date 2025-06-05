<?php

namespace App\Http\Controllers\Api;


use Exception;
use Carbon\Carbon;
use App\Models\Dosen;
use App\Models\StatusForm;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Models\RequestPembimbing;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BimbinganResource;
use App\Http\Resources\NilaiBimbinganResource;
use App\Http\Resources\TugasAkhirResource;
use Illuminate\Validation\ValidationException;


class TugasAkhirController extends Controller
{
    public function indexForAdmin()
    {
        try {
            $requests = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->get();
            return ApiResponse::success(TugasAkhirResource::collection($requests));
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
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
                'status_aktif' => 'required|in:0,1,2',
            ]);

            // Find the record by ID with eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])->find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Update the status_aktif
            $tugasAkhir->status_aktif = $validatedData['status_aktif'];
            $tugasAkhir->save();

            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Pendaftaran Ujian TA sukses');
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
                    // Simpan ke folder private
                    $filePath = $request->file($field)->storeAs("private/tugas_akhir/{$tugasAkhir->id}", $originalName);
                    // Path yang disimpan di DB harus sama dengan lokasi file di storage
                    $privatePath = "private/tugas_akhir/{$tugasAkhir->id}/{$originalName}";

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
                    $filePath = $request->file($field)->storeAs("private/tugas_akhir/{$tugasAkhir->id}", $originalName);
                    $privatePath = "private/tugas_akhir/{$tugasAkhir->id}/{$originalName}";

                    $berkas = $tugasAkhir->berkas()->where('nama_berkas', $namaBerkas)->first();
                    if ($berkas) {
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

    public function updateValidationStatusForAdmin(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'status_valid' => 'required|in:1,2', // 1 = valid, 2 = invalid
            ]);

            // Find the TugasAkhir record
            $tugasAkhir = TugasAkhir::find($id);

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Update the validation status
            $tugasAkhir->status_valid = $validatedData['status_valid'];
            $tugasAkhir->save();

            $statusMessage = $validatedData['status_valid'] == 1 ? 'valid' : 'invalid';
            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), "Tugas Akhir status diubah menjadi $statusMessage");
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
            $tugasAkhir = TugasAkhir::with(['proposal', 'bimbingan', 'ujianTA', 'requestPembimbing'])
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
            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
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
                ->where('dosen_pembimbing_id', $dosenId)
                ->where('status_aktif', 1)
                ->get();

            $requestPembimbing = RequestPembimbing::with(['tugasAkhir.mahasiswa', 'tugasAkhir.kategori'])
                ->where('status_request', 0)
                ->where('dosen_id', $dosenId)
                ->orderBy('tanggal_request', 'asc')
                ->limit(5)
                ->get();

            $tablePermintaanBimbingan = $requestPembimbing
                ->map(function ($item) {
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

            if ($tugasAkhir->isEmpty()) {
                return ApiResponse::success(null, 'Tidak ada tugas akhir');
            }

            // Sekarang $tugasAkhir adalah collection, map() aman digunakan
            $taPerBab = $tugasAkhir->map(function ($item) {
                return [
                    'id_ta' => $item->id,
                    'bab_tertinggi' => $item->bimbingan
                        ->where('status_verifikasi', 1)
                        ->max('bab')
                ];
            });

            // Kelompokkan berdasarkan bab (1-5)
            $statistikBab = collect(range(1, 5))->map(function ($bab) use ($taPerBab) {
                $taDiBab = $taPerBab->where('bab_tertinggi', $bab);
                return [
                    'bab' => $bab,
                    'jumlah_ta' => $taDiBab->count(),
                    'daftar_ta' => $taDiBab->values()->all()
                ];
            });

            //jadwal ujian hari ini
            $ujianHariIni = $tugasAkhir->flatMap(function ($ta) {
                return $ta->ujianTA
                    ->where('tanggal_ujian', now()->format('Y-m-d'))
                    ->where('status_ujian', 'aktif')
                    ->map(function ($ujian) use ($ta) {
                        return [
                            'tanggal_ujian' => $ujian->tanggal_ujian,
                            'nama' => $ta->mahasiswa->name,
                            'waktu' => date('H:i', strtotime($ujian->sesi->waktu_mulai)),
                            'sesi' => $ujian->sesi->nama_sesi ?? null,
                            'ruang' => $ujian->ruang->nama_ruang ?? null
                        ];
                    });
            });
            $ujianTerjadwal = $tugasAkhir->sum(function ($ta) {
                return $ta->ujianTA
                    ->where('status_ujian', 'aktif')
                    ->count();
            });

            $jumlahReqPembimbing = $requestPembimbing
                ->where('status_request', 0)
                ->where('dosen_id', $dosenId)
                ->count();

            $data = [
                'doughnat_chart' => $statistikBab->pluck('jumlah_ta', 'bab') ?? null,
                'summary_card' => [
                    'ta_yang_dibimbing' => $tugasAkhir->count() ?? null,
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
                'requestPembimbing'
            ])
                ->where('status_aktif', 1);

            // Fitur pencarian berdasarkan nama mahasiswa atau NIM
            if ($request->search) {
                $query->whereHas('mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                });
            }

            // Fitur filter berdasarkan kategori
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
            ])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->get();

            $daftarDosen = Dosen::select('name', 'nik', 'id')->get();
            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'kategori' => [
                        'kategori_id' => $item->kategori->id ?? null,
                        'nama_kategori' => $item->kategori->nama_kategori ?? null,
                    ],
                    'tahun_ajaran' => $item->tahunAjaran ? $item->tahunAjaran->tahun_ajaran : null,
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing->id ?? null,
                        'name' => $item->dosenPembimbing->name ?? null,
                    ],
                ];
            });

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

    public function putPlottingPembimbingByAdmin(Request $request, $tugasAkhirId)
    {
        try {
            $validatedData = $request->validate([
                'dosen_pembimbing_id' => 'required|exists:dosen,id',
            ]);

            // Cek apakah Tugas Akhir dengan ID tersebut ada
            $tugasAkhir = TugasAkhir::findOrFail($tugasAkhirId);

            // Update dosen pembimbing
            $tugasAkhir->dosen_pembimbing_id = $validatedData['dosen_pembimbing_id'];
            $tugasAkhir->save();

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
                ->where('status_aktif', 1)
                ->whereNotNull('dosen_pembimbing_id'); // hanya yang sudah punya dosen pembimbing

            if ($request->search) {
                $query->whereHas('mahasiswa', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('nim', 'like', '%' . $request->search . '%');
                });
            }

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

            $tugasAkhir = $query->orderBy('id', 'asc')->paginate(2);

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
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
                'bimbingan:id,tugas_akhir_id,catatan,bab,status_verifikasi,tanggal_bimbingan',
                'kategori:id,nama_kategori',
                'nilaiBimbingan',
                'tahunAjaran:id,tahun_ajaran',
            ])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            Carbon::setLocale('id');
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
                'bimbingan' => $tugasAkhir->bimbingan->map(function ($bimbingan) {
                    return [
                        'bimbingan_id' => $bimbingan->id,
                        'catatan' => $bimbingan->catatan,
                        'bab' => $bimbingan->bab,
                        'status_verifikasi' => $bimbingan->status_verifikasi,
                        'tanggal_bimbingan' => $bimbingan->tanggal_bimbingan
                            ? Carbon::parse($bimbingan->tanggal_bimbingan)->translatedFormat('l, d F Y')
                            : null,
                    ];
                }),
                'nilai_bimbingan' => [
                    'nilai_bimbingan_id' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->id : null,
                    'rerata_bimbingan' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->rerata_bimbingan : null,
                    'disiplin' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->disiplin : null,
                    'komunikasi' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->komunikasi : null,
                    'skill_pengetahuan' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->skill_pengetahuan : null,
                    'tingkah_laku' => $tugasAkhir->nilaiBimbingan ? $tugasAkhir->nilaiBimbingan->tingkah_laku : null,
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
                ->where('status_aktif', 1)
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

            $tugasAkhir = $query->paginate(10);

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
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
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            $daftarDosen = Dosen::select('id', 'name', 'nik')->get();

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
                'dosen_penguji' => $dosenPenguji, // <-- tambahkan ini
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
                ->where('status_aktif', 1)
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

            // Fitur filter status (0,1,2 saja, null tidak masuk)
            if ($request->has('status')) {
                $statusValidList = array_filter(
                    explode(',', $request->status),
                    fn($v) => in_array($v, ['0', '1', '2'], true)
                );
                if (count($statusValidList)) {
                    $query->whereIn('status_valid', $statusValidList);
                }
            }

            $tugasAkhir = $query->paginate(10);

            $statusForm = StatusForm::where('nama_form', 'Tugas Akhir')
                ->select('id', 'is_open')
                ->first();


            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
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
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
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
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'ujianTA'
            ])
                ->where('status_aktif', 1)
                ->where('status_valid', 1);

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

            // Fitur filter status penjadwalan/kelulusan
            if ($request->has('status')) {
                $status = $request->status;
                $query->where(function ($q) use ($status) {
                    if ($status === 'menunggu') {
                        $q->whereDoesntHave('ujianTA', function ($q2) {
                            $q2->where('status_ujian', 'aktif');
                        });
                    } elseif ($status === 'terjadwal') {
                        $q->whereHas('ujianTA', function ($q2) {
                            $q2->where('status_ujian', 'aktif');
                        })->whereNull('status_kelulusan');
                    } elseif (in_array($status, ['1', '2', '3', '4'])) {
                        $q->where('status_kelulusan', $status);
                    }
                });
            }

            $tugasAkhir = $query->paginate(10);

            $data = collect($tugasAkhir->items())->map(function ($item) {
                Carbon::setLocale('id');
                $ujianTA = $item->ujianTA->where('status_ujian', 'aktif')->first();
                if (is_null($item->status_kelulusan)) {
                    if ($ujianTA) {
                        $status_kategori = 'terjadwal';
                    } else {
                        $status_kategori = 'menunggu';
                    }
                } else {
                    // Sudah ada status kelulusan
                    switch ($item->status_kelulusan) {
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
                            $status_kategori = 'tidak_lulus_ulang_ta_diujikan_lagi';
                            break;
                        default:
                            $status_kategori = 'tidak_diketahui';
                    }
                }
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'status_kelulusan' => $item->status_kelulusan ?? null,
                    'status_kategori' => $status_kategori ?? null,
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing->id ?? null,
                        'name' => $item->dosenPembimbing->name ?? null,
                    ],
                    'ujian_ta' => $ujianTA ? [
                        'ujian_ta_id' => $ujianTA->id,
                        'tanggal_ujian' => $ujianTA->tanggal_ujian
                            ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                            : null,
                        'waktu_mulai' => $ujianTA->sesi->waktu_mulai ?? null,
                        'waktu_selesai' => $ujianTA->sesi->waktu_selesai ?? null,
                        'ruang' => $ujianTA->ruang->nama_ruang ?? null,
                        'status_ujian' => $ujianTA->status_ujian ?? null
                    ] : [],
                    'dosen_penguji' => $item->dosenPenguji
                        ? $item->dosenPenguji->map(function ($penguji) {
                            return [
                                'dosen_penguji_id' => $penguji->id ?? null,
                                'name' => $penguji->name ?? null,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
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

            return ApiResponse::success($result, 'List Tugas Akhir yang siap untuk dijadwalkan berhasil diambil');
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
                'ujianTA'
            ])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            $ujianTA = $tugasAkhir->ujianTA->where('status_ujian', 'aktif')->first();
            Carbon::setLocale('id');

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
                    'tanggal_ujian' => $ujianTA->tanggal_ujian
                        ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                        : null,
                    'waktu_mulai' => $ujianTA->sesi->waktu_mulai ?? null,
                    'waktu_selesai' => $ujianTA->sesi->waktu_selesai ?? null,
                    'ruang' => $ujianTA->ruang->nama_ruang ?? null,
                    'status_ujian' => $ujianTA->status_ujian
                ] : [],
                'dosen_penguji' =>
                    $tugasAkhir->dosenPenguji
                    ? $tugasAkhir->dosenPenguji->map(function ($penguji) {
                        return [
                            'dosen_penguji_id' => $penguji->id,
                            'name' => $penguji->name,
                            'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                        ];
                    })->values()
                    : [],
            ];
            return ApiResponse::success($data, 'Detail Tugas Akhir untuk penjadwalan ujian berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
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
                'bimbingan'
            ])
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
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

            $result = [
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
            $query = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'ujianTA.sesi',
                'ujianTA.ruang'
            ])
                ->where('status_aktif', 1)
                ->whereHas('dosenPenguji', function ($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId);
                })
                ->whereHas('ujianTA', function ($q) {
                    $q->where('status_ujian', 'aktif');
                });

            // Fitur search berdasarkan nama mahasiswa, NIM, atau judul tugas akhir
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('mahasiswa', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('nim', 'like', '%' . $request->search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $request->search . '%');
                });
            }

            // Filter sesi
            if ($request->sesi) {
                $query->whereHas('ujianTA.sesi', function ($q) use ($request) {
                    $q->where('nama_sesi', 'like', '%' . $request->sesi . '%');
                });
            }

            // Urutkan berdasarkan tanggal ujian terdekat
            $query->whereHas('ujianTA', function ($q) {
                $q->where('status_ujian', 'aktif');
            });

            // Join ke tabel ujian_ta agar bisa orderBy tanggal_ujian
            $query->leftJoin('ujian_ta', function ($join) {
                $join->on('tugas_akhir.id', '=', 'ujian_ta.tugas_akhir_id')
                    ->where('ujian_ta.status_ujian', 'aktif');
            })
                ->orderBy('ujian_ta.tanggal_ujian', 'asc')
                ->select('tugas_akhir.*');

            $tugasAkhir = $query->paginate(10);

            $data = collect($tugasAkhir->items())->map(function ($item) {
                Carbon::setLocale('id');
                $ujianTA = $item->ujianTA->where('status_ujian', 'aktif')->first();
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id ?? null,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'kategori' => [
                        'kategori_id' => $item->kategori->id ?? null,
                        'nama_kategori' => $item->kategori->nama_kategori ?? null,
                    ],
                    'tanggal_ujian' => $ujianTA && $ujianTA->tanggal_ujian
                        ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                        : null,
                    'sesi' => [
                        'sesi_id' => $ujianTA && $ujianTA->sesi ? $ujianTA->sesi->id : null,
                        'nama_sesi' => $ujianTA && $ujianTA->sesi ? $ujianTA->sesi->nama_sesi : null,
                        'waktu_mulai' => $ujianTA && $ujianTA->sesi ? date('H:i', strtotime($ujianTA->sesi->waktu_mulai)) : null,
                        'waktu_selesai' => $ujianTA && $ujianTA->sesi ? date('H:i', strtotime($ujianTA->sesi->waktu_selesai)) : null,
                    ],
                    'ruang' => $ujianTA && $ujianTA->ruang ? $ujianTA->ruang->nama_ruang : null,
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

            return ApiResponse::success($result, 'List Ujian TA yang diujikan berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function detailUjianForDosen($tugasAkhirId)
    {
        try {

            $dosenId = Auth::id();
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'tahunAjaran:id,tahun_ajaran',
                'kategori:id,nama_kategori',
                'ujianTA',
                'berkas'
            ])
                ->where('id', $tugasAkhirId)
                ->whereHas('ujianTA')
                ->where('status_aktif', 1)
                ->where('status_valid', 1) // Hanya ambil yang sudah validasi
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Detail ujian untuk TA ini tidak ditemukan');
            }

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
                    ];
                })
                ->values();
            Carbon::setLocale('id');
            $ujianTA = $tugasAkhir->ujianTA->where('status_ujian', 'aktif')->first();
            $data =
                [
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
                    'judul_tugas_akhir' => $tugasAkhir->judul_tugas_akhir ?? null,
                    'berkas' => $naskahBerkas,
                    'tahun_ajaran' => [
                        'tahun_ajaran_id' => $tugasAkhir->tahunAjaran->id ?? null,
                        'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                    ],
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $tugasAkhir->dosenPembimbing->id ?? null,
                        'name' => $tugasAkhir->dosenPembimbing->name ?? null,
                    ],
                    'ujian_ta' => $ujianTA ? [
                        'ujian_ta_id' => $ujianTA->id,
                        'tanggal_ujian' => $ujianTA->tanggal_ujian
                            ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                            : null,

                        'waktu_mulai' => date('H:i', strtotime($ujianTA->sesi->waktu_mulai)) ?? null,
                        'waktu_selesai' => date('H:i', strtotime($ujianTA->sesi->waktu_selesai)) ?? null,
                        'ruang' => $ujianTA->ruang->nama_ruang ?? null,
                        'status_ujian' => $ujianTA->status_ujian
                    ] : [],
                    'dosen_penguji' => $tugasAkhir->dosenPenguji
                        ? $tugasAkhir->dosenPenguji->map(function ($penguji) use ($dosenId) {
                            $isLogin = $penguji->id == $dosenId;
                            $nilai = $penguji->pivot->nilaiUjianTA;
                            return [
                                'dosen_penguji_id' => $penguji->id,
                                'name' => $penguji->name,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                                // Hanya tampilkan nilai jika dosen login
                                'nilai_ujian_ta' => $isLogin && $nilai ? [
                                    'id' => $nilai->id,
                                    'kualitas_tata_laporan' => $nilai->kualitas_tata_laporan,
                                    'kemampuan_teknis' => $nilai->kemampuan_teknis,
                                    'kualitas_produk' => $nilai->kualitas_produk,
                                    'presentasi' => $nilai->presentasi,
                                    'rerata_ujian_ta' => $nilai->rerata_ujian_ta,
                                ] : null,
                                'revisi' => $penguji->pivot->revisi ?? null,
                            ];
                        })->values()
                        : [],
                ];

            return ApiResponse::success($data, 'Detail ujian tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatusKelulusanByDosenPembimbing(Request $request, $tugasAkhirId)
    {
        try {
            $request->validate([
                'status_kelulusan' => 'required|in:1,2,3,4'
            ]);

            $dosenId = Auth::id();
            $tugasAkhir = TugasAkhir::with(['dosenPenguji'])
                ->where('id', $tugasAkhirId)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }
            // Cek apakah dosen yang sedang login adalah dosen pembimbing
            $isKetua = $tugasAkhir->dosenPenguji
                ->filter(function ($penguji) use ($dosenId) {
                    return $penguji->id == $dosenId && ($penguji->pivot->jabatan_penguji ?? null) === 'sekretaris';
                })
                ->isNotEmpty();
            if (!$isKetua) {
                return ApiResponse::error('Anda bukan sekretaris pada tugas akhir ini.', 403);
            }

            $tugasAkhir->status_kelulusan = $request->status_kelulusan;
            $tugasAkhir->save();

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
