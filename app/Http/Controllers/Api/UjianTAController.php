<?php

namespace App\Http\Controllers\Api;

use App\Models\Dosen;
use App\Models\UjianTA;
use App\Models\Semester;
use App\Models\TugasAkhir;
use App\Models\TahunAjaran;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Exports\UjianTAExport;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\UjianTAResource;
use App\Http\Resources\TugasAkhirResource;
use Exception;
use Illuminate\Validation\ValidationException;


class UjianTAController extends Controller
{

    // Menampilkan data tugas akhir yang siap ujian (tidak menampilkan data tugas akhir yang sudah memiliki jadwal ujian)
    public function getTAReadyForAdmin()
    {
        try {
            $calonPesertaUjian = TugasAkhir::with('dosenPenguji')
                ->where('status_aktif', 1)
                ->where('status_valid', 1)
                ->whereDoesntHave('ujianTA')
                ->get();

            // Gunakan Resource untuk memformat data
            return ApiResponse::success(TugasAkhirResource::collection($calonPesertaUjian), 'Data tugas akhir yang siap ujian berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data tugas akhir yang siap ujian gagal diambil: ' . $e->getMessage(), 500);
        }
    }
    public function index()
    {
        try {
            $listUjianTA = DB::table('ujian_ta')
                ->select('ujian_ta.id', 'ujian_ta.tugas_akhir_id', 'ujian_ta.tanggal_ujian', 'ujian_ta.ruang_id', 'ujian_ta.sesi_id')
                ->join('tugas_akhir', 'ujian_ta.tugas_akhir_id', '=', 'tugas_akhir.id')
                ->join('mahasiswa', 'tugas_akhir.mahasiswa_id', '=', 'mahasiswa.id')
                ->join('ruang', 'ujian_ta.ruang_id', '=', 'ruang.id')
                ->join('sesi', 'ujian_ta.sesi_id', '=', 'sesi.id')
                ->paginate(10);

            return ApiResponse::success($listUjianTA, 'Data ujian TA berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal diambil: ' . $e->getMessage(), 500);
        }
    }

    public function storeJadwalUjianTAforAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
                'tanggal_ujian' => 'required|date|after_or_equal:today',
                'ruang_id' => 'required|exists:ruang,id',
                'sesi_id' => 'required|exists:sesi,id',
                'status_ujian' => 'required|in:aktif,non-aktif'
            ]);

            // Ambil semester dan tahun ajaran default dari database
            $semesterDefault = Semester::where('is_default', 1)->first();
            $tahunAjaranDefault = TahunAjaran::where('is_default', 1)->first();

            if (!$semesterDefault) {
                return ApiResponse::error('Semester default tidak ditemukan. Hubungi administrator.', 422);
            }

            if (!$tahunAjaranDefault) {
                return ApiResponse::error('Tahun ajaran default tidak ditemukan. Hubungi administrator.', 422);
            }

            // Tambahkan semester_id dan tahun_ajaran_id ke validated data
            $validated['semester_id'] = $semesterDefault->id;
            $validated['tahun_ajaran_id'] = $tahunAjaranDefault->id;

            // 1. Cek apakah tugas akhir sudah memiliki jadwal ujian
            $cekJadwalUjian = UjianTA::where('tugas_akhir_id', $validated['tugas_akhir_id'])
                ->where('status_ujian', 'aktif')
                ->exists();
            if ($cekJadwalUjian) {
                return ApiResponse::error('Tugas akhir sudah memiliki jadwal ujian aktif', 422);
            }

            // ✅ PERBAIKAN: Cek conflict ruang + sesi + tanggal (kombinasi lengkap)
            $cekRuangSesiConflict = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('ruang_id', $validated['ruang_id'])
                ->where('sesi_id', $validated['sesi_id'])
                ->where('status_ujian', 'aktif')
                ->exists();

            if ($cekRuangSesiConflict) {
                return ApiResponse::error('Kombinasi ruang, sesi, dan tanggal ini sudah terpakai', 422);
            }

            // 3. Ambil data tugas akhir dengan dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($validated['tugas_akhir_id']);

            // Pastikan tugas akhir memiliki dosen penguji
            if ($tugasAkhir->dosenPenguji->isEmpty()) {
                return ApiResponse::error('Tugas akhir belum memiliki dosen penguji', 422);
            }

            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            // ✅ PERBAIKAN: Cek conflict dosen (tanggal + sesi sama, ruang boleh beda)
            $cekDosenConflict = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('sesi_id', $validated['sesi_id'])
                ->where('status_ujian', 'aktif')
                ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                    $query->whereIn('dosen_id', $listDosenPenguji);
                })
                ->exists();

            if ($cekDosenConflict) {
                // Beri info dosen mana yang bentrok dan di ruang mana
                $conflictData = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                    ->where('sesi_id', $validated['sesi_id'])
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                        $query->whereIn('dosen_id', $listDosenPenguji);
                    })
                    ->with([
                        'ruang:id,nama_ruang',
                        'sesi:id,nama_sesi',
                        'tugasAkhir.dosenPenguji' => function ($query) use ($listDosenPenguji) {
                            $query->whereIn('dosen_id', $listDosenPenguji);
                        }
                    ])
                    ->first();

                $conflictDosen = $conflictData->tugasAkhir->dosenPenguji->pluck('name')->join(', ');
                $conflictRuang = $conflictData->ruang->nama_ruang;
                $conflictSesi = $conflictData->sesi->nama_sesi;

                return ApiResponse::error(
                    "Dosen penguji ($conflictDosen) sudah ujian pada tanggal dan sesi yang sama di $conflictRuang. Pilih sesi yang berbeda untuk ruang yang sama.",
                    422
                );
            }

            // 5. Buat jadwal ujian
            $ujianTA = UjianTA::create($validated);

            return ApiResponse::success(
                $ujianTA->load([
                    'tugasAkhir.mahasiswa:id,nim,name',
                    'tugasAkhir.dosenPenguji:id,name',
                    'ruang:id,nama_ruang',
                    'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
                    'semester:id,nama_semester',
                    'tahunAjaran:id,tahun_ajaran'
                ]),
                'Data ujian TA berhasil ditambahkan'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }


    public function updateJadwalUjianTAforAdmin(Request $request, $id)
    {
        try {
            $ujianTA = UjianTA::findOrFail($id);

            // Jika status_ujian non-aktif, buat baru, jangan update yang lama
            if ($ujianTA->status_ujian === 'non-aktif') {
                // Ambil data tugas akhir terkait
                $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($ujianTA->tugas_akhir_id);

                $validated = $request->validate([
                    'tanggal_ujian' => 'required|date|after_or_equal:today',
                    'ruang_id' => 'required|exists:ruang,id',
                    'sesi_id' => 'required|exists:sesi,id',
                    'status_ujian' => 'required|in:aktif,non-aktif'
                ]);

                // Ambil semester dan tahun ajaran default
                $semesterDefault = Semester::where('is_default', 1)->first();
                $tahunAjaranDefault = TahunAjaran::where('is_default', 1)->first();
                if ($semesterDefault) {
                    $validated['semester_id'] = $semesterDefault->id;
                }
                if ($tahunAjaranDefault) {
                    $validated['tahun_ajaran_id'] = $tahunAjaranDefault->id;
                }
                $validated['tugas_akhir_id'] = $tugasAkhir->id;

                // Cek conflict ruang+sesi+tanggal
                $cekRuangSesiConflict = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                    ->where('ruang_id', $validated['ruang_id'])
                    ->where('sesi_id', $validated['sesi_id'])
                    ->where('status_ujian', 'aktif')
                    ->exists();
                if ($cekRuangSesiConflict) {
                    return ApiResponse::error('Kombinasi ruang, sesi, dan tanggal ini sudah terpakai', 422);
                }

                // Cek conflict dosen
                $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();
                $cekDosenConflict = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                    ->where('sesi_id', $validated['sesi_id'])
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                        $query->whereIn('dosen_id', $listDosenPenguji);
                    })
                    ->exists();
                if ($cekDosenConflict) {
                    // Beri info dosen mana yang bentrok dan di ruang mana
                    $conflictData = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                        ->where('sesi_id', $validated['sesi_id'])
                        ->where('status_ujian', 'aktif')
                        ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                            $query->whereIn('dosen_id', $listDosenPenguji);
                        })
                        ->with([
                            'ruang:id,nama_ruang',
                            'sesi:id,nama_sesi',
                            'tugasAkhir.dosenPenguji' => function ($query) use ($listDosenPenguji) {
                                $query->whereIn('dosen_id', $listDosenPenguji);
                            }
                        ])
                        ->first();

                    $conflictDosen = $conflictData->tugasAkhir->dosenPenguji->pluck('name')->join(', ');
                    $conflictRuang = $conflictData->ruang->nama_ruang;
                    $conflictSesi = $conflictData->sesi->nama_sesi;

                    return ApiResponse::error(
                        "Dosen penguji ($conflictDosen) sudah ujian pada tanggal dan sesi yang sama di $conflictRuang. Pilih sesi yang berbeda untuk ruang yang sama.",
                        422
                    );
                }

                // Buat jadwal ujian baru
                $ujianTABaru = UjianTA::create($validated);

                return ApiResponse::success(
                    $ujianTABaru->load([
                        'tugasAkhir.mahasiswa:id,nim,name',
                        'tugasAkhir.dosenPenguji:id,name',
                        'ruang:id,nama_ruang',
                        'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
                        'semester:id,nama_semester',
                        'tahunAjaran:id,tahun_ajaran'
                    ]),
                    'Data ujian TA baru berhasil ditambahkan'
                );
            }

            // --- Jika status_ujian aktif, lanjut update seperti biasa ---
            $validated = $request->validate([
                'tanggal_ujian' => 'sometimes|required|date|after_or_equal:today',
                'ruang_id' => 'sometimes|required|exists:ruang,id',
                'sesi_id' => 'sometimes|required|exists:sesi,id',
                'status_ujian' => 'sometimes|required|in:aktif,non-aktif'
            ]);

            // Ambil semester dan tahun ajaran default jika tidak ada di request
            if (!isset($validated['semester_id'])) {
                $semesterDefault = Semester::where('is_default', 1)->first();
                if ($semesterDefault) {
                    $validated['semester_id'] = $semesterDefault->id;
                }
            }

            if (!isset($validated['tahun_ajaran_id'])) {
                $tahunAjaranDefault = TahunAjaran::where('is_default', 1)->first();
                if ($tahunAjaranDefault) {
                    $validated['tahun_ajaran_id'] = $tahunAjaranDefault->id;
                }
            }

            // Cek conflict ruang+sesi+tanggal
            $cekRuangSesiConflict = false;
            if (isset($validated['tanggal_ujian']) || isset($validated['ruang_id']) || isset($validated['sesi_id'])) {
                $cekRuangSesiConflict = UjianTA::where('id', '!=', $id)
                    ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                    ->where('ruang_id', $validated['ruang_id'] ?? $ujianTA->ruang_id)
                    ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                    ->where('status_ujian', 'aktif')
                    ->exists();
            }

            if ($cekRuangSesiConflict) {
                return ApiResponse::error('Kombinasi ruang, sesi, dan tanggal ini sudah terpakai', 422);
            }

            // 2. Ambil data tugas akhir dengan dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($ujianTA->tugas_akhir_id);

            // Pastikan tugas akhir memiliki dosen penguji
            if ($tugasAkhir->dosenPenguji->isEmpty()) {
                return ApiResponse::error('Tugas akhir belum memiliki dosen penguji', 422);
            }

            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            // Cek conflict dosen (tanggal + sesi sama, ruang boleh beda)
            $cekDosenConflict = false;
            if (isset($validated['tanggal_ujian']) || isset($validated['sesi_id'])) {
                $cekDosenConflict = UjianTA::where('id', '!=', $id)
                    ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                    ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                        $query->whereIn('dosen_id', $listDosenPenguji);
                    })
                    ->exists();
            }

            if ($cekDosenConflict) {
                // Beri info dosen mana yang bentrok dan di ruang mana
                $conflictData = UjianTA::where('id', '!=', $id)
                    ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                    ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                    ->where('status_ujian', 'aktif')
                    ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                        $query->whereIn('dosen_id', $listDosenPenguji);
                    })
                    ->with([
                        'ruang:id,nama_ruang',
                        'sesi:id,nama_sesi',
                        'tugasAkhir.dosenPenguji' => function ($query) use ($listDosenPenguji) {
                            $query->whereIn('dosen_id', $listDosenPenguji);
                        }
                    ])
                    ->first();

                $conflictDosen = $conflictData->tugasAkhir->dosenPenguji->pluck('name')->join(', ');
                $conflictRuang = $conflictData->ruang->nama_ruang;
                $conflictSesi = $conflictData->sesi->nama_sesi;

                return ApiResponse::error(
                    "Dosen penguji ($conflictDosen) sudah ujian pada tanggal dan sesi yang sama di $conflictRuang. Pilih sesi yang berbeda untuk ruang yang sama.",
                    422
                );
            }

            // 3. Update data
            $ujianTA->update($validated);

            return ApiResponse::success(
                $ujianTA->fresh()->load([
                    'tugasAkhir.mahasiswa:id,nim,name',
                    'tugasAkhir.dosenPenguji:id,name',
                    'ruang:id,nama_ruang',
                    'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
                    'semester:id,nama_semester',
                    'tahunAjaran:id,tahun_ajaran'
                ]),
                'Data ujian TA berhasil diubah'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal diubah: ' . $e->getMessage(), 500);
        }
    }
    public function jadwalUjianForMahasiswa()
    {
        try {
            $mahasiswaId = auth()->id();

            // Ambil tugas akhir aktif milik mahasiswa
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)
                ->where('status_aktif', 1)
                ->first();

            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Belum ada Tugas Akhir aktif');
            }

            // Ambil ujian TA terbaru (latest) beserta relasi
            $ujianTA = UjianTA::with([
                'ruang:id,nama_ruang',
                'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
            ])
                ->where('tugas_akhir_id', $tugasAkhir->id)
                ->where('status_ujian', 'aktif')
                ->orderByDesc('id')
                ->first();

            if (!$ujianTA) {
                return ApiResponse::success(null, 'Belum ada jadwal ujian TA');
            }

            // Ambil dosen penguji dari tabel pivot
            $dosenPengujiPivot = DosenPenguji::where('tugas_akhir_id', $tugasAkhir->id)->get();
            $dosenPenguji = [];
            foreach ($dosenPengujiPivot as $penguji) {
                $dosen = Dosen::find($penguji->dosen_id);
                $dosenPenguji[] = [
                    'dosen' => [
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'jabatan' => $penguji->jabatan_penguji,
                    ],
                ];
            }

            $result = [
                'ujian_ta' => [
                    'tugas_akhir' => $ujianTA->tugas_akhir_id,
                    'id' => $ujianTA->id,
                    'ruang' => $ujianTA->ruang ? [
                        'id' => $ujianTA->ruang->id,
                        'nama' => $ujianTA->ruang->nama_ruang,
                    ] : null,
                    'tanggal' => $ujianTA->tanggal_ujian,
                    'sesi' => $ujianTA->sesi ? [
                        'id' => $ujianTA->sesi->id,
                        'nama' => $ujianTA->sesi->nama_sesi,
                        'waktu_mulai' => $ujianTA->sesi->waktu_mulai,
                        'waktu_selesai' => $ujianTA->sesi->waktu_selesai,
                    ] : null,
                ],
                'dosen_penguji' => $dosenPenguji,
            ];

            return ApiResponse::success($result, 'Data ujian TA berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal mengambil data ujian TA: ' . $e->getMessage(), 500);
        }
    }

    public function exportUjianTA(Request $request)
    {
        try {
            $search = $request->get('search');
            $status = $request->get('status');
            $tahunAjaranId = $request->get('tahun_ajaran_id');

            return Excel::download(
                new UjianTAExport($search, $status, $tahunAjaranId),
                'ujian_ta_filtered.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat export data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Dosen Sekretaris

    public function editNilaiUjianTA(Request $request, $ujianTaId)
    {
        try {
            $request->validate([
                'edit_nilai' => 'required|boolean'
            ]);

            $dosenId = Auth::id();

            $ujianTA = UjianTA::with('tugasAkhir.dosenPenguji')->findOrFail($ujianTaId);

            // Cek apakah dosen adalah sekretaris
            $dosenPenguji = $ujianTA->tugasAkhir->dosenPenguji->firstWhere('id', $dosenId);
            if (!$dosenPenguji || ($dosenPenguji->pivot->jabatan_penguji ?? null) !== 'sekretaris') {
                return ApiResponse::error('Hanya sekretaris yang dapat mengatur izin edit nilai.', 403);
            }

            $ujianTA->update([
                'edit_nilai' => $request->edit_nilai
            ]);

            $message = $request->edit_nilai
                ? 'Dosen penguji kini dapat mengedit nilai ujian TA'
                : 'Dosen penguji tidak dapat mengedit nilai ujian TA';

            return ApiResponse::success([
                'ujian_ta_id' => $ujianTA->id,
                'edit_nilai' => $ujianTA->edit_nilai
            ], $message);

        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
