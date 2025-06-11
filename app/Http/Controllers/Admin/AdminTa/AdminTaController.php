<?php

namespace App\Http\Controllers\Admin\AdminTa;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Dosen;
use App\Models\TugasAkhir;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToArray;

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

            // Filter akses_ta
            if ($request->has('akses_ta') && $request->akses_ta !== null && $request->akses_ta !== '') {
                $query->where('akses_ta', (int) $request->akses_ta);
            }
            // Filter angkatan
            if ($request->has('angkatan') && $request->angkatan) {
                $angkatanList = explode(',', $request->angkatan);
                $angkatanList = array_map('trim', $angkatanList);
                $query->whereRaw("CONCAT('20', SUBSTRING(nim, 4, 2)) IN (" . implode(',', array_fill(0, count($angkatanList), '?')) . ")", $angkatanList);
            }

            // Pagination (default 10 per page)
            $mahasiswa = $query->orderByRaw('CAST(SUBSTRING(nim, 6) AS UNSIGNED) DESC')->paginate(10);

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

            // Filter akses_ta jika ada di request
            if ($request->has('akses_ta') && $request->akses_ta !== null && $request->akses_ta !== '') {
                $query->where('akses_ta', (int) $request->akses_ta);
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

}
