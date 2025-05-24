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

    public function updateTugasAkhirForMahasiswa(Request $request, $id)
    {
        try {
            $mahasiswaId = Auth::id();

            // Validasi semua atribut yang tidak boleh null
            $validatedData = $request->validate([
                'judul_tugas_akhir' => 'required|string|max:255',
                'krs' => 'required|file|mimes:pdf',
                'persetujuan_pembimbing' => 'required|file|mimes:pdf',
                'sertifikat_kompetensi' => 'required|file|mimes:pdf',
                'toeic' => 'required|file|mimes:pdf',
                'berita_acara_semhas' => 'required|file|mimes:pdf',
                'naskah_ta' => 'required|file|mimes:pdf',
                'masa_perpanjangan_studi' => 'nullable|date',
                'file_transkrip' => 'required|file|mimes:pdf',
            ]);

            // Cari TugasAkhir milik mahasiswa dengan eager loading
            $tugasAkhir = TugasAkhir::with(['dosenPembimbing', 'bimbingan'])
                ->where('id', $id)
                ->where('mahasiswa_id', $mahasiswaId) // Pastikan mahasiswa memiliki record ini
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::error('Tugas Akhir tidak ditemukan atau unauthorized', 404);
            }

            // Tambahkan kondisi untuk status_valid
            if (!is_null($tugasAkhir->status_valid) && !in_array($tugasAkhir->status_valid, [2])) {
                return ApiResponse::error('Tugas Akhir tidak dapat diperbarui karena status validasi tidak sesuai', 403);
            }

            // Perbarui data TugasAkhir
            $tugasAkhir->judul_tugas_akhir = $validatedData['judul_tugas_akhir'];
            $tugasAkhir->status_valid = 0; // Set status_valid menjadi 0 (menunggu validasi)
            $tugasAkhir->save();

            $berkasList = [
                'krs',
                'persetujuan_pembimbing',
                'sertifikat_kompetensi',
                'toeic',
                'berita_acara_semhas',
                'naskah_ta',
                'masa_perpanjangan_studi',
                'file_transkrip'
            ];


            foreach ($berkasList as $berkasName) {
                if ($request->hasFile($berkasName)) {
                    $originalName = $request->file($berkasName)->getClientOriginalName();
                    $filePath = $request->file($berkasName)->storeAs("tugas_akhir/{$tugasAkhir->id}", $originalName);

                    $berkas = $tugasAkhir->berkas()->where('nama_berkas', $berkasName)->first();
                    if ($berkas) {
                        $berkas->file_path = $filePath;
                        $berkas->status_valid = 0;
                        $berkas->save();
                    } else {
                        $tugasAkhir->berkas()->create([
                            'nama_berkas' => $berkasName,
                            'file_path' => $filePath,
                            'status_valid' => 0,
                        ]);
                    }
                }
            }

            // Kembalikan data TugasAkhir yang diperbarui menggunakan resource
            return ApiResponse::success(new TugasAkhirResource($tugasAkhir), 'Tugas Akhir berhasil diperbarui dan menunggu validasi');
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
                ->sortByDesc('tanggal_ujian')
                ->first();
            $data = [
                'tugas_akhir_id' => $tugasAkhir->id,
                'dosen_pembimbing' => $tugasAkhir->dosenPembimbing->name ?? null,
                'proposal' => [
                    'id' => $proposal->id ?? null,
                    'status' => $tugasAkhir->proposal ? $tugasAkhir->proposal->status_proposal : null,
                    'catatan' => $tugasAkhir->proposal ? $tugasAkhir->proposal->catatan_proposal : null,
                ],
                'status_valid' => $tugasAkhir->status_valid,
                'nilai_akhir' => $tugasAkhir->nilai_akhir ? $tugasAkhir->nilai_akhir : null,
                'bimbingan' => [
                    'total_bimbingan' => $tugasAkhir->bimbingan->count() ?? null,
                    'progress_bimbingan' => $babTerbesar ?? null,

                ],
                'tanggal_ujian' => $ujianTA ? $ujianTA->tanggal_ujian : null,

                'request_pembimbing' => [
                    'id' => $tugasAkhir->requestPembimbing ? $tugasAkhir->requestPembimbing->id : null,
                    'stattus' => $tugasAkhir->requestPembimbing ? $tugasAkhir->requestPembimbing->status_request : null
                ],

            ];

            return ApiResponse::success($data, 'Data tugas akhir berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function setDashboardByDosen()
    {
        try {
            $dosenId = Auth::id();
            $tugasAkhir = TugasAkhir::with(['bimbingan', 'ujianTA', 'requestPembimbing', 'mahasiswa', 'kategori'])
                ->where('dosen_pembimbing_id', $dosenId)
                ->where('status_aktif', 1)
                ->get();
            $requestPembimbing = RequestPembimbing::with(['tugasAkhir.mahasiswa', 'tugasAkhir.kategori'])->get();

            if ($tugasAkhir->isEmpty()) {
                return ApiResponse::success(null, 'Tidak ada tugas akhir');
            }

            // Sekarang $tugasAkhir adalah collection, map() aman digunakan
            $taPerBab = $tugasAkhir->map(function ($ta) {
                return [
                    'id_ta' => $ta->id,
                    'bab_tertinggi' => $ta->bimbingan
                        ->where('status_verifikasi', 1)
                        ->max('bab') ?? 0
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
                            'waktu' => date('H:i', strtotime($ujian->sesi->waktu_mulai)),
                            'sesi' => $ujian->sesi->nama_sesi ?? null,
                            'ruang' => $ujian->ruang->nama_ruang ?? null
                        ];
                    });
            });


            $tablePermintaanBimbingan = $requestPembimbing
                ->where('status_request', 0)
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
            $data = [
                'doughnat_chart' => $statistikBab->pluck('jumlah_ta', 'bab') ?? null,
                'summary_card' => [
                    'ta_yang_dibimbing' => $tugasAkhir->count() ?? null,
                    'ujian_terjadwal' => $tugasAkhir->sum(function ($ta) {
                        return $ta->ujianTA->where('status_ujian', 1)->count() ?? null;
                    }),
                    'permintaan_bimbingan' => $tugasAkhir->sum(function ($ta) use ($dosenId) {
                        return $ta->requestPembimbing
                            ->where('dosen_id', $dosenId)
                            ->where('status_request', 0)
                            ->count() ?? null;
                    }),
                ],
                'ujian_hari_ini' => $ujianHariIni ?? null,
                'permintaan_bimbingan' => $tablePermintaanBimbingan->values() ?? null,
            ];

            return ApiResponse::success($data, 'Data tugas akhir berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
    // Fitur Bimbingan Admin TA

    public function tabelPlottingByAdmin()
    {
        try {
            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'dosenPembimbing', 'kategori', 'requestPembimbing'])
                ->where('status_aktif', 1)->get();

            $data = $tugasAkhir->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->mahasiswa->nim,
                    'mahasiswa' => $item->mahasiswa->name,
                    'dosen_pembimbing' => $item->dosenPembimbing ? $item->dosenPembimbing->name : null,
                    'kategori' => $item->kategori ? $item->kategori->nama_kategori : null,
                    'request_pembimbing' => $item->requestPembimbing ? $item->requestPembimbing->status_request : null,
                ];
            });

            return ApiResponse::success($data, 'Data Tugas Akhir berhasil diambil');
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


    // Fitur Proposal Admin TA

    public function tabelPengajuanByAdmin()
    {
        try {

            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'proposal', 'proposal.berkas'])
                ->where('status_aktif', 1)
                ->get();
            $data = $tugasAkhir->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nim' => $item->mahasiswa->nim,
                    'mahasiswa' => $item->mahasiswa->name,
                    'judul_proposal' => $item->proposal ? $item->proposal->judul_proposal : null,
                    'status_proposal' => $item->proposal ? $item->proposal->status_proposal : null,
                    'berkas' => $item->proposal ? $item->proposal->berkas : null,

                ];
            });
            return ApiResponse::success($data, 'Data Tugas Akhir berhasil diambil');

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur Proposal Admin TA

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
}
