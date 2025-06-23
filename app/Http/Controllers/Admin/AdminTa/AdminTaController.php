<?php

namespace App\Http\Controllers\Admin\AdminTa;

use FPDM;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Dosen;
use App\Models\UjianTA;
use App\Models\TugasAkhir;
use App\Models\JabatanDosen;
use App\Models\NilaiUjianTA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class AdminTaController extends Controller
{
    public function dashboard()
    {
        try {
            $tugasAkhir = TugasAkhir::with(['mahasiswa', 'ujianTA', 'proposal'])
                ->where('status_aktif', 1)
                ->get();

            if (!$tugasAkhir) {
                return ApiResponse::success(null, 'Tidak ada tugas akhir yang aktif');
            }

            $proposal = $tugasAkhir->pluck('proposal')->filter();
            $tanggal_ujian = $tugasAkhir->pluck('ujianTA')->flatten(1);
            $tableValidasi = $tugasAkhir
                ->filter(function ($ta) {
                    return $ta->proposal && $ta->proposal->status_proposal == 0;
                })
                ->map(function ($ta) {
                    return [
                        'proposal_id' => $ta->proposal->id ?? null,
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'mahasiswa' => $ta->mahasiswa->name ?? null,
                        'judul' => $ta->proposal->judul_proposal ?? null,
                        'status' => $ta->proposal->status_proposal ?? null,
                    ];
                })
                ->values();

            $tabelUjian = $tugasAkhir
                ->where('status_valid', '0')
                ->map(function ($ta) {
                    return [
                        'tugas_akhir_id' => $ta->id ?? null,
                        'nim' => $ta->mahasiswa->nim ?? null,
                        'mahasiswa' => $ta->mahasiswa->name ?? null,
                        'judul' => $ta->judul_tugas_akhir ?? null,
                        'status' => $ta->status_valid ?? null,
                    ];
                })
                ->values();

            // Tambahan: Mahasiswa dengan akses_ta = 1
            $mahasiswaAksesTA = User::where('akses_ta', 1)
                ->where('status', 1)
                ->get();

            // Tambahan: Dosen dengan akses_ta = 1
            $dosenAksesTA = Dosen::where('akses_ta', 1)
                ->where('status', 1)
                ->get();

            $data = [
                'validasi_berkas' => [
                    'proposal' => [
                        'total' => $proposal->count() ?? null,
                        'valid' => $proposal->where('status_proposal', 1)->count() ?? null,
                        'menunggu' => $proposal->where('status_proposal', 0)->count() ?? null,
                    ],
                    'ujian_tugas_akhir' => [
                        'total' => $tugasAkhir->where('status_valid', '!=', null)->count() ?? null,
                        'valid' => $tugasAkhir->where('status_valid', 1)->count() ?? null,
                        'menunggu' => $tugasAkhir->where('status_valid', '0')->count() ?? null,
                    ],
                ],
                'summary_card' => [
                    'tugas_akhir_aktif' => $tugasAkhir->count() ?? null,
                    'ujian_terjadwal' => $tanggal_ujian->count() ?? null,
                ],
                'tabel_validasi_berkas_sempro' => $tableValidasi->take(3) ?? null,
                'tabel_validasi_berkas_ujian' => $tabelUjian->take(3) ?? null,
                'mahasiswa_akses_ta' => $mahasiswaAksesTA->count() ?? null,
                'dosen_akses_ta' => $dosenAksesTA->count() ?? null,
            ];

            return ApiResponse::success($data, 'Data berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur User (List Mahasiswa & Set Akses TA untuk Mahasiswa )

    public function tabelListMahasiswaForAdmin(Request $request)
    {
        try {
            $query = User::where('status', 1);

            // Search by nama atau nim
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('nim', 'like', "%$search%");
                });
            }

            // Filter by akses_ta jika ada
            if ($request->has('akses_ta') && $request->akses_ta !== '') {
                $statusList = explode(',', $request->akses_ta);
                $statusList = array_map('trim', $statusList);
                $query->whereIn('akses_ta', $statusList);
            }

            // Filter angkatan
            if ($request->has('angkatan') && $request->angkatan) {
                $angkatanList = explode(',', $request->angkatan);
                $angkatanList = array_map('trim', $angkatanList);
                $query->whereRaw("CONCAT('20', SUBSTRING(nim, 4, 2)) IN (" . implode(',', array_fill(0, count($angkatanList), '?')) . ")", $angkatanList);
            }

            // Pagination (default 10 per page)
            $mahasiswa = $query
                ->orderByRaw('CAST(SUBSTRING(nim, 4, 2) AS UNSIGNED) DESC')
                ->orderByRaw('CAST(SUBSTRING(nim, 6) AS UNSIGNED) ASC')
                ->paginate(10);

            // Mapping data
            $data = $mahasiswa->getCollection()->map(function ($mhs) {
                $angkatan = substr($mhs->nim, 3, 2);
                return [
                    'mahasiswa_id' => $mhs->id,
                    'name' => $mhs->name,
                    'nim' => $mhs->nim,
                    'email' => $mhs->email,
                    'angkatan' => "20" . $angkatan,
                    'akses_ta' => $mhs->akses_ta,
                ];
            });

            // Ambil daftar angkatan unik (urut desc)
            $angkatanListAll = User::where('status', 1)
                ->selectRaw("DISTINCT(CONCAT('20', SUBSTRING(nim, 4, 2))) as angkatan")
                ->orderByDesc('angkatan')
                ->pluck('angkatan')
                ->filter()
                ->map(fn($v) => (int) $v)
                ->sortDesc()
                ->values();

            // Ambil angkatan terbaru
            $angkatanTerbaru = $angkatanListAll->first();

            // Ambil 5 tahun ke belakang dari angkatan terbaru
            $angkatanLimit = collect();
            if ($angkatanTerbaru) {
                for ($i = 0; $i < 5; $i++) {
                    $angkatanLimit->push($angkatanTerbaru - $i);
                }
            }

            // Filter angkatan_list agar hanya 5 tahun terakhir
            $angkatan_list = $angkatanListAll->filter(fn($v) => $angkatanLimit->contains($v))->values();

            // Return dengan struktur pagination & daftar angkatan
            $result = [
                'angkatan_list' => $angkatan_list,
                'data' => $data,
                'pagination' => [
                    'current_page' => $mahasiswa->currentPage(),
                    'last_page' => $mahasiswa->lastPage(),
                    'per_page' => $mahasiswa->perPage(),
                    'total' => $mahasiswa->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data mahasiswa berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function setAksesTAMahasiswaByAdmin(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:mahasiswa,id',
            'akses_ta' => 'required|boolean',
        ]);

        try {
            // Update semua user yang id-nya ada di array
            User::whereIn('id', $request->ids)->update(['akses_ta' => $request->akses_ta]);

            return ApiResponse::success(null, 'Akses TA berhasil diubah untuk banyak user');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    // Fitur User (List Dosen & Set Akses TA untuk Dosen )

    public function tabelListDosenForAdmin(Request $request)
    {
        try {
            $query = Dosen::where('status', 1);


            // Filter by akses_ta jika ada
            if ($request->has('akses_ta') && $request->akses_ta !== '') {
                $statusList = explode(',', $request->akses_ta);
                $statusList = array_map('trim', $statusList);
                $query->whereIn('akses_ta', $statusList);
            }

            // Search by nama, nik, atau nim
            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('nik', 'like', "%$search%");
                });
            }

            // Pagination (default 10 per page)
            $dosen = $query->orderBy('name', 'asc')->paginate(10);

            // Mapping data
            $data = $dosen->getCollection()->map(function ($dosen) {
                return [
                    'dosen_id' => $dosen->id,
                    'name' => $dosen->name,
                    'nik' => $dosen->nik,
                    'email' => $dosen->email,
                    'akses_ta' => $dosen->akses_ta,
                ];
            });

            // Return dengan struktur pagination
            $result = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $dosen->currentPage(),
                    'last_page' => $dosen->lastPage(),
                    'per_page' => $dosen->perPage(),
                    'total' => $dosen->total(),
                ],
            ];

            return ApiResponse::success($result, 'Data dosen berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
    //setAksesTA mahasiswa
    public function setAksesTADosenByAdmin(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:dosen,id',
            'akses_ta' => 'required|boolean',
        ]);

        try {
            // Update semua user yang id-nya ada di array
            Dosen::whereIn('id', $request->ids)->update(['akses_ta' => $request->akses_ta]);

            return ApiResponse::success(null, 'Akses TA berhasil diubah untuk banyak user');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function cetakBeritaAcaraUjian($ujianTaId)
    {
        try {
            // Ambil data Ujian TA beserta relasi yang dibutuhkan
            $ujianTA = UjianTA::with([
                'tugasAkhir.mahasiswa',
                'tugasAkhir.dosenPembimbing',
                'tugasAkhir.dosenPenguji',
                'tugasAkhir.nilaiBimbingan',
                'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
                'ruang:id,nama_ruang',
            ])->findOrFail($ujianTaId);

            $tugasAkhir = $ujianTA->tugasAkhir;

            // Ambil nilai proposal dan bimbingan
            $nilaiProposal = $tugasAkhir->nilai_proposal ?? null;
            $nilaiBimbingan1 = $tugasAkhir->nilaiBimbingan->rerata_bimbingan ?? null;
            $nilaiBimbingan2 = $tugasAkhir->nilaiBimbingan->rerata_bimbingan ?? null; // Jika ada dua, sesuaikan
            $totalNilaiTugasAkhir = $ujianTA->nilai_akhir ?? null;

            // Ambil nilai ujian dari dosen penguji
            $nilaiUjianTA = NilaiUjianTA::where('ujian_ta_id', $ujianTA->id)->get();

            // Rata-rata nilai ujian (dibulatkan 2 desimal)
            $nilaiRataRataUjian = round($nilaiUjianTA->avg('rerata_ujian_ta'), 2);

            // Nilai per jabatan penguji
            $penguji = $tugasAkhir->dosenPenguji;
            $ketua = $penguji->firstWhere('pivot.jabatan_penguji', 'ketua');
            $sekretaris = $penguji->firstWhere('pivot.jabatan_penguji', 'sekretaris');
            $pengujiBiasa = $penguji->where('pivot.jabatan_penguji', 'penguji');

            $nilaiKetua = $nilaiUjianTA->where('dosen_id', optional($ketua)->id)->first();
            $nilaiSekretaris = $nilaiUjianTA->where('dosen_id', optional($sekretaris)->id)->first();
            $nilaiPenguji = $nilaiUjianTA->whereIn('dosen_id', $pengujiBiasa->pluck('id'))->pluck('rerata_ujian_ta')->avg();

            // Ambil kepala program studi (jabatan_id = 3)
            $kepalaProdi = JabatanDosen::with('dosen')
                ->where('jabatan_id', 3)
                ->where('is_active', 1)
                ->first();

            // Ambil divisi tugas akhir (jabatan_id = 5)
            $divisiTA = JabatanDosen::with('dosen')
                ->where('jabatan_id', 5)
                ->where('is_active', 1)
                ->first();

            // Data identitas
            $judul_tugas_akhir = $tugasAkhir->judul_tugas_akhir ?? '-';
            $nama_mahasiswa = $tugasAkhir->mahasiswa->name ?? '-';
            $nim_mahasiswa = $tugasAkhir->mahasiswa->nim ?? '-';

            Carbon::setLocale('id');
            $tanggal_ujian = $ujianTA->tanggal_ujian ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('d F Y') : '-';
            $hari_tanggal_ujian = $ujianTA->tanggal_ujian ? Carbon::parse($ujianTA->tanggal_ujian)->translatedFormat('l, d F Y') : '-';
            $waktu_ujian = $ujianTA->sesi && $ujianTA->sesi->waktu_mulai ? substr($ujianTA->sesi->waktu_mulai, 0, 5) . ' WIB' : '-';

            $fields = [
                'judul_tugas_akhir-1' => $judul_tugas_akhir,
                'judul_tugas_akhir-2' => $judul_tugas_akhir,
                'judul_tugas_akhir-3' => $judul_tugas_akhir,
                'nama_kepala_program_studi' => $kepalaProdi ? $kepalaProdi->dosen->name : null,
                'nip_kepala_program_studi' => $kepalaProdi ? $kepalaProdi->dosen->nik : null,
                'nama_divisi_tugas_akhir' => $divisiTA ? $divisiTA->dosen->name : null,
                'nip_divisi_tugas_akhir' => $divisiTA ? $divisiTA->dosen->nik : null,
                'nama_ketua_penguji-1' => $ketua?->name ?? '-',
                'nama_ketua_penguji-3' => $ketua?->name ?? '-',
                'nama_ketua_penguji-4' => $ketua?->name ?? '-',
                'nama_mahasiswa-1' => $nama_mahasiswa,
                'nama_mahasiswa-2' => $nama_mahasiswa,
                'nama_mahasiswa-3' => $nama_mahasiswa,
                'nama_mahasiswa-4' => $nama_mahasiswa,
                'nama_penguji-1' => $pengujiBiasa->first()?->name ?? '-',
                'nama_penguji-3' => $pengujiBiasa->first()?->name ?? '-',
                'nama_penguji-4' => $pengujiBiasa->first()?->name ?? '-',
                'nama_penguji-5' => $pengujiBiasa->first()?->name ?? '-',
                'nama_sekretaris_penguji-1' => $sekretaris?->name ?? '-',
                'nama_sekretaris_penguji-3' => $sekretaris?->name ?? '-',
                'nama_sekretaris_penguji-4' => $sekretaris?->name ?? '-',
                'nik_ketua_penguji-1' => $ketua?->nik ?? '-',
                'nik_ketua_penguji-3' => $ketua?->nik ?? '-',
                'nik_ketua_penguji-4' => $ketua?->nik ?? '-',
                'nik_penguji-1' => $pengujiBiasa->first()?->nik ?? '-',
                'nik_penguji-3' => $pengujiBiasa->first()?->nik ?? '-',
                'nik_penguji-4' => $pengujiBiasa->first()?->nik ?? '-',
                'nik_penguji-5' => $pengujiBiasa->first()?->nik ?? '-',
                'nik_sekretaris_penguji-1' => $sekretaris?->nik ?? '-',
                'nik_sekretaris_penguji-3' => $sekretaris?->nik ?? '-',
                'nik_sekretaris_penguji-4' => $sekretaris?->nik ?? '-',
                'nim_mahasiswa-1' => $nim_mahasiswa,
                'nim_mahasiswa-2' => $nim_mahasiswa,
                'nim_mahasiswa-3' => $nim_mahasiswa,
                'nim_mahasiswa-4' => $nim_mahasiswa,
                'tanggal_ujian-1' => $tanggal_ujian,
                'tanggal_ujian-2' => $tanggal_ujian,
                'tanggal_ujian-3' => $hari_tanggal_ujian,
                'tanggal_ujian-4' => $tanggal_ujian,
                'tanggal_ujian-5' => $hari_tanggal_ujian,
                'tanggal_ujian-6' => $tanggal_ujian,
                'tanggal_ujian-7' => $hari_tanggal_ujian,
                'tanggal_ujian-8' => $tanggal_ujian,
                'waktu_ujian-1' => $waktu_ujian,
                'waktu_ujian-2' => $waktu_ujian,
                'waktu_ujian-3' => $waktu_ujian,
                'waktu_ujian-4' => $waktu_ujian,
                // Nilai
                'nilai_bimbingan_ta-1' => $nilaiBimbingan1 ?: '-',
                'nilai_bimbingan_ta-2' => $nilaiBimbingan2 ?: '-',
                'nilai_proposal_ta' => $nilaiProposal ?: '-',
                'nilai_rata-rata_ujian' => $nilaiRataRataUjian ?: '-',
                'nilai_ujian_ketua_penguji' => ($nilaiKetua && $nilaiKetua->rerata_ujian_ta) ? $nilaiKetua->rerata_ujian_ta : '-',
                'nilai_ujian_penguji' => $nilaiPenguji ?: '-',
                'nilai_ujian_sekretaris_penguji' => ($nilaiSekretaris && $nilaiSekretaris->rerata_ujian_ta) ? $nilaiSekretaris->rerata_ujian_ta : '-',
                'rata-rata_nilai_ujian_ta' => $nilaiRataRataUjian ?: '-',
                'total_nilai_tugas_akhir' => $totalNilaiTugasAkhir ?: '-',
            ];

            // Generate PDF jika template tersedia
            $templatePath = storage_path('app/template_berita_acara.pdf');
            if (!file_exists($templatePath)) {
                return ApiResponse::error("Template tidak ditemukan di: $templatePath", 404);
            }

            $pdf = new FPDM($templatePath);
            $pdf->Load($fields, true);
            $pdf->Merge();

            return response($pdf->Output('S', 'berita_acara_ujian.pdf'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="berita_acara_ujian.pdf"');
        } catch (Exception $e) {
            return ApiResponse::error('Gagal mengambil data berita acara ujian: ' . $e->getMessage(), 500);
        }
    }

    public function downloadLembarRevisi()
    {
        $path = storage_path('app/template_lembar_revisi.pdf');
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan');
        }
        return response()->download($path, 'template_lembar_revisi.pdf');
    }

    public function tabelJabatanDosenForAdmin()
    {
        try {
            // Ambil semua dosen beserta relasi jabatannya (hanya yang aktif)
            $dosenList = Dosen::orderBy('name')->get();

            // Mapping data untuk response
            $data = $dosenList->map(function ($dosen) {
                return [
                    'dosen_id' => $dosen->id,
                    'name' => $dosen->name,
                    'nik' => $dosen->nik,
                    'email' => $dosen->email,
                    'akses_ta' => $dosen->akses_ta,
                    'jabatans' => $dosen->jabatans->map(function ($jabatan) {
                        return [
                            'jabatan_id' => $jabatan->id,
                            'name' => $jabatan->name,
                            'description' => $jabatan->description,
                            'is_active' => $jabatan->pivot->is_active,
                        ];
                    }),
                ];
            });

            return ApiResponse::success($data, 'Data dosen dan jabatannya berhasil diambil');
        } catch (Exception $e) {
            return ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function setKaprodiDosenByAdmin(Request $request)
    {
        $request->validate([
            'dosen_id' => 'required|exists:dosen,id',
        ]);


        try {
            DB::transaction(function () use ($request) {
                $dosenId = $request->input('dosen_id');
                $dosen = Dosen::findOrFail($dosenId);

                // Cek apakah dosen yang dipilih sedang menjabat sebagai Divisi TA
                if ($dosen->jabatans->contains('id', 5)) {
                    throw new Exception('Dosen yang menjabat sebagai Divisi TA tidak dapat dijadikan Kaprodi.', 422);
                }
                // Validasi bahwa dosen yang dipilih aktif dan memiliki akses TA
                if ($dosen->status != 1 || $dosen->akses_ta != 1) {
                    throw new Exception('Dosen yang dipilih harus aktif dan memiliki akses TA.', 422);
                }

                // Hapus semua jabatan Kaprodi yang ada
                JabatanDosen::where('jabatan_id', 3)->delete();

                // Tetapkan dosen yang dipilih sebagai Kaprodi baru
                JabatanDosen::create([
                    'dosen_id' => $dosenId,
                    'jabatan_id' => 3,
                    'is_active' => 1,
                ]);
            });

            return ApiResponse::success(null, 'Jabatan Kaprodi berhasil diupdate.');
        } catch (Exception $e) {
            $statusCode = is_numeric($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500;
            return ApiResponse::error('Gagal mengupdate Kaprodi: ' . $e->getMessage(), $statusCode);
        }
    }

    public function setDivisiTADosenByAdmin(Request $request)
    {
        $request->validate([
            'dosen_id' => 'required|exists:dosen,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $dosenId = $request->input('dosen_id');
                $dosen = Dosen::findOrFail($dosenId);

                // Cek apakah dosen yang dipilih sedang menjabat sebagai Divisi TA
                if ($dosen->jabatans->contains('id', 3)) {
                    throw new Exception('Dosen yang menjabat sebagai Kaprodi tidak dapat dijadikan Divisi TA.', 422);
                }

                // Validasi bahwa dosen yang dipilih aktif dan memiliki akses TA
                if ($dosen->status != 1 || $dosen->akses_ta != 1) {
                    throw new Exception('Dosen yang dipilih harus aktif dan memiliki akses TA.', 422);
                }

                // Hapus semua jabatan Divisi TA yang ada
                JabatanDosen::where('jabatan_id', 5)->delete();

                // Tetapkan dosen yang dipilih sebagai Divisi TA baru
                JabatanDosen::create([
                    'dosen_id' => $dosenId,
                    'jabatan_id' => 5,
                    'is_active' => 1,
                ]);
            });

            return ApiResponse::success(null, 'Jabatan Divisi TA berhasil diupdate.');
        } catch (Exception $e) {
            $statusCode = is_numeric($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500;
            return ApiResponse::error('Gagal mengupdate Divisi TA, ' . $e->getMessage(), $statusCode);
        }
    }
}