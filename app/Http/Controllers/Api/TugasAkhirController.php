<?php

namespace App\Http\Controllers\Api;

use App\Models\RequestPembimbing;
use Exception;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TugasAkhirResource;
use App\Models\Dosen;
use App\Models\StatusForm;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


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

    //belum di test
    public function memberiNilaiTAbyDosen(Request $request, $id)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'nilai_ujian_ta' => 'required|numeric|min:0|max:100', // Nilai harus antara 0 dan 100
                'revisi' => 'required|string|max:1000',    // Catatan maksimal 1000 karakter
            ]);

            // Ambil data TugasAkhir berdasarkan ID dengan relasi dosenPenguji dan ujianTA
            $tugasAkhir = TugasAkhir::with(['dosenPenguji', 'ujianTA.sesi'])
                ->where('id', $id)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan', 404);
            }

            // Pastikan dosen yang sedang login adalah salah satu dosen penguji
            $dosenId = $request->user()->id; // Ambil ID dosen yang sedang login
            $isDosenPenguji = $tugasAkhir->dosenPenguji->contains('id', $dosenId);

            if (!$isDosenPenguji) {
                return ApiResponse::error('Anda tidak memiliki izin untuk memberikan nilai pada Tugas Akhir ini', 403);
            }

            // Pastikan ujian TA sedang terlaksana
            $ujianTA = $tugasAkhir->ujianTA;
            if (!$ujianTA) {
                return ApiResponse::error('Ujian TA belum dijadwalkan untuk Tugas Akhir ini', 400);
            }

            // Periksa apakah ujian sedang berlangsung berdasarkan tanggal dan sesi
            $currentDateTime = now();
            $ujianStartTime = $ujianTA->tanggal_ujian->startOfDay()->addHours($ujianTA->sesi->start_time);
            $ujianEndTime = $ujianTA->tanggal_ujian->startOfDay()->addHours($ujianTA->sesi->end_time);

            if ($currentDateTime->lt($ujianStartTime) || $currentDateTime->gt($ujianEndTime)) {
                return ApiResponse::error('Nilai hanya dapat diberikan saat ujian sedang berlangsung', 403);
            }

            // Update nilai dan catatan pada TugasAkhir
            $tugasAkhir->nilai_ta = $validatedData['nilai_ujian_ta'];
            $tugasAkhir->catatan_ta = $validatedData['revisi'];
            $tugasAkhir->save();

            // Kembalikan respons sukses
            return ApiResponse::success(
                new TugasAkhirResource($tugasAkhir),
                'Nilai dan catatan revisi berhasil diberikan kepada Tugas Akhir'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
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
                        'nama_mahasiswa' => $item->tugasAkhir->mahasiswa->name ?? null,
                        'nim_mahasiswa' => $item->tugasAkhir->mahasiswa->nim ?? null,
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
                            'nama_mahasiswa' => $ta->mahasiswa->name,
                            'waktu' => $ujian->sesi ? date('H:i', strtotime($ujian->sesi->waktu_mulai)) : null,
                            'sesi' => $ujian->sesi->nama_sesi ?? null,
                            'ruang' => $ujian->ruang->nama_ruang ?? null
                        ];
                    });
            });


            $data = [
                'doughnat_chart' => $statistikBab->pluck('jumlah_ta', 'bab') ?? null,
                'summary_card' => [
                    'ta_yang_dibimbing' => $tugasAkhir->count() ?? null,
                    'ujian_terjadwal' => $tugasAkhir->sum(function ($ta) {
                        return $ta->ujianTA->where('status_ujian', 'aktif')->count() ?? null;
                    }),
                    'permintaan_bimbingan' => $tablePermintaanBimbingan->count() ?? null,
                ],
                'ujian_hari_ini' => $ujianHariIni ?? null,
                'permintaan_bimbingan' => $tablePermintaanBimbingan->values() ?? null,
            ];

            return ApiResponse::success($data, 'Data tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Plotting dosen pembimbing TA

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

    // End Fitur Plotting dosen pembimbing TA

    // Fitur Bimbingan Admin TA (belum menggunakan select, kurang paginate)

    public function tabelPlottingByAdmin(Request $request)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa',
                'dosenPembimbing',
                'kategori',
                'requestPembimbing'
            ])
                ->where('status_aktif', 1)
                ->paginate(10);

            $data = collect($tugasAkhir->items())->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'nim' => $item->mahasiswa->nim,
                    'mahasiswa' => $item->mahasiswa->name,
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'kategori' => $item->kategori ? $item->kategori->nama_kategori : null,
                    'request_pembimbing' => $item->requestPembimbing ? $item->requestPembimbing->status_request : null,
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

    public function tabelListBimbinganByAdmin()
    {
        try {
            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'dosenPembimbing', 'bimbingan', 'kategori'])
                ->where('status_aktif', 1)->get();

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->mahasiswa->nim,
                    'mahasiswa' => $item->mahasiswa->name,
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'kategori' => $item->kategori ? $item->kategori->nama_kategori : null,
                    'jumlah_bimbingan' => $item->bimbingan ? $item->bimbingan->count() : 0,
                ];
            });

            return ApiResponse::success($data, 'Data Tugas Akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function plottingPembimbingByAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'kategori', 'tahunAjaran'])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->get()
            ;
            $daftarDosen = Dosen::select('name', 'nik', 'id')->get();
            $data = $tugasAkhir->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->mahasiswa->nim,
                    'mahasiswa' => $item->mahasiswa->name,
                    'kategori' => $item->kategori ? $item->kategori->nama_kategori : null,
                    'tahun_ajaran' => $item->tahunAjaran ? $item->tahunAjaran->tahun_ajaran : null,
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

    public function detailBimbinganByAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'dosenPembimbing', 'bimbingan', 'kategori', 'nilaiBimbingan', 'tahunAjaran'])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();


            $data = [
                'id' => $tugasAkhir->id,
                'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                'nama_mahasiswa' => $tugasAkhir->mahasiswa->name ?? null,
                'kategori' => $tugasAkhir->kategori->nama_kategori ?? null,
                'tahun_ajaran' => $tugasAkhir->tahunAjaran->tahun_ajaran ?? null,
                'dosen_pembimbing' => $tugasAkhir->dosenPembimbing->name ?? null,
                'bimbingan' => $tugasAkhir->bimbingan ?? null,
                'nilai_bimbingan' => $tugasAkhir->nilaiBimbingan ?? null,
            ];
            return ApiResponse::success($data, 'Data Tugas Akhir berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // End Fitur Bimbingan Admin TA


    // Fitur Ujian TA Admin TA (sudah menggunakan select, kurang paginate)

    public function tabelPlottingPengujiByAdmin()
    {
        try {

            $tugasAkhir = TugasAkhir::with([
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
                ->having('bimbingan_count', '>=', 9)
                ->get();

            $statusForm = StatusForm::where('nama_form', 'Tugas Akhir')
                ->select('id', 'is_open')
                ->first();

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_proposal' => $item->proposal->judul_proposal ?? null,
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'dosen_penguji' => $item->dosenPenguji
                        ? $item->dosenPenguji->map(function ($penguji) {
                            return [
                                'id' => $penguji->id,
                                'name' => $penguji->name,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
                ];
            });

            return ApiResponse::success(
                [
                    'status_form' => $statusForm,
                    'list_data' => $data
                ],
                'List Tugas Akhir dengan minimal 9 bimbingan disetujui berhasil diambil'
            );

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function plottingPengujiByAdmin($tugasAkhirId)
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name,nik',
                'proposal:id,judul_proposal',
                'tahunAjaran:id,tahun_ajaran',
                'kategori:id,nama_kategori'
            ])
                ->where('status_aktif', 1)
                ->where('id', $tugasAkhirId)
                ->first();
            $daftarDosen = Dosen::select('id', 'name', 'nik')->get();
            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'mahasiswa' => [
                    'nim' => $tugasAkhir->mahasiswa->nim ?? null,
                    'name' => $tugasAkhir->mahasiswa->name ?? null,
                ],
                'judul_proposal' => $tugasAkhir->proposal->judul_proposal ?? null,
                'dosen_pembimbing' => [
                    'id' => $tugasAkhir->dosenPembimbing ? $tugasAkhir->dosenPembimbing->id : null,
                    'name' => $tugasAkhir->dosenPembimbing ? $tugasAkhir->dosenPembimbing->name : null,
                    'nik' => $tugasAkhir->dosenPembimbing ? $tugasAkhir->dosenPembimbing->nik : null,
                ],
                'kategori' => $tugasAkhir->kategori ? $tugasAkhir->kategori->nama_kategori : null,
                'tahun_ajaran' => $tugasAkhir->tahunAjaran ? $tugasAkhir->tahunAjaran->tahun_ajaran : null,
                'daftar_dosen' => $daftarDosen
            ];
            return ApiResponse::success($data, 'Data untuk plotting penguji berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function tabelPendaftarUjianByAdmin()
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
            ])
                ->where('status_aktif', 1)
                ->whereIn('status_valid', [0, 1, 2]) // Hanya ambil yang belum validasi atau ditolak
                ->get()
            ;

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'status_valid' => $item->status_valid,
                ];
            });

            return ApiResponse::success($data, 'List Tugas Akhir yang mendaftar ujian berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function tabelPenjadwalanUjianByAdmin()
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'ujianTA'
            ])
                ->where('status_aktif', 1)
                ->where('status_valid', 1) // Hanya ambil yang sudah validasi
                ->get();

            Carbon::setLocale('id');
            $data = $tugasAkhir->map(function ($item) {
                $ujianTA = $item->ujianTA->where('status_ujian', 'aktif')->first();
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'dosen_pembimbing' => [
                        'id' => $item->dosenPembimbing ? $item->dosenPembimbing->id : null,
                        'name' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    ],
                    'ujian_ta' => $ujianTA ? [
                        'id' => $ujianTA->id,
                        'tanggal_ujian' => $ujianTA->tanggal_ujian
                            ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                            : null,
                        'waktu_mulai' => $ujianTA->sesi ? $ujianTA->sesi->waktu_mulai : null,
                        'waktu_selesai' => $ujianTA->sesi ? $ujianTA->sesi->waktu_selesai : null,
                        'ruang' => $ujianTA->ruang ? $ujianTA->ruang->nama_ruang : null,
                        'status_ujian' => $ujianTA->status_ujian
                    ] : [],
                    'dosen_penguji' => $item->dosenPenguji
                        ? $item->dosenPenguji->map(function ($penguji) {
                            return [
                                'id' => $penguji->id,
                                'name' => $penguji->name,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],
                ];
            });

            return ApiResponse::success($data, 'List Tugas Akhir yang siap untuk dijadwalkan berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
    public function tabelBeritaAcaraByAdmin()
    {
        try {
            $tugasAkhir = TugasAkhir::with([
                'mahasiswa:id,nim,name',
                'dosenPembimbing:id,name',
                'dosenPenguji:id,name',
                'ujianTA'
            ])
                ->where('status_aktif', 1)
                ->whereNotNull('status_kelulusan')
                ->get();

            Carbon::setLocale('id');
            $data = $tugasAkhir->map(function ($item) {
                $ujianTA = $item->ujianTA->where('status_ujian', 'aktif')->first();
                return [
                    'tugas_akhir_id' => $item->id,
                    'mahasiswa' => [
                        'mahasiswa_id' => $item->mahasiswa->id,
                        'nim' => $item->mahasiswa->nim ?? null,
                        'name' => $item->mahasiswa->name ?? null,
                    ],
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? null,
                    'status_kelulusan' => $item->status_kelulusan,
                    'dosen_pembimbing' => [
                        'dosen_pembimbing_id' => $item->dosenPembimbing ? $item->dosenPembimbing->id : null,
                        'name' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    ],
                    'ujian_ta' => $ujianTA ? [
                        'ujian_ta_id' => $ujianTA->id,
                        'tanggal_ujian' => $ujianTA->tanggal_ujian
                            ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y')
                            : null,
                        'waktu_mulai' => $ujianTA->sesi ? $ujianTA->sesi->waktu_mulai : null,
                        'waktu_selesai' => $ujianTA->sesi ? $ujianTA->sesi->waktu_selesai : null,
                        'ruang' => $ujianTA->ruang ? $ujianTA->ruang->nama_ruang : null,
                        'status_ujian' => $ujianTA->status_ujian
                    ] : [],
                    'dosen_penguji' => $item->dosenPenguji
                        ? $item->dosenPenguji->map(function ($penguji) {
                            return [
                                'dosen_penguji_id' => $penguji->id,
                                'name' => $penguji->name,
                                'jabatan_penguji' => $penguji->pivot->jabatan_penguji ?? null,
                            ];
                        })->values()
                        : [],

                    'berita_acara' => null,
                    'catatan_revisi' => null,
                ];
            });

            return ApiResponse::success($data, 'List Tugas Akhir yang sudah memiliki berita acara berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // End Fitur Ujian TA Admin TA


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
                    'kategori_ta' => $tugasAkhir->kategori ? [
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
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data tugas akhir: ' . $e->getMessage(), 500);
        }
    }
}
