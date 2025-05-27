<?php

namespace App\Http\Controllers\Api;

use App\Models\Dosen;
use App\Models\UjianTA;
use App\Models\TugasAkhir;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UjianTAResource;
use App\Http\Resources\TugasAkhirResource;
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
            $listUjianTA = \DB::table('ujian_ta')
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


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tugas_akhir_id' => 'required|exists:tugas_akhir,id',
                'tanggal_ujian' => 'required|date',
                'ruang_id' => 'required|exists:ruang,id',
                'sesi_id' => 'required|exists:sesi,id',
                'semester_id' => 'required|exists:semester,id',
                'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                'status_ujian' => 'required|in:aktif,non-aktif'
            ]);

            // Cek apakah tugas akhir sudah memiliki jadwal ujian
            $cekJadwalUjian = UjianTA::where('tugas_akhir_id', $validated['tugas_akhir_id'])->exists();
            if ($cekJadwalUjian) {
                return ApiResponse::error('Tugas akhir sudah memiliki jadwal ujian', 422);
            }

            // Cek apakah ada jadwal bentrok ruangan dan sesinya
            $cekJadwalBentrok = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('ruang_id', $validated['ruang_id'])
                ->where('sesi_id', $validated['sesi_id'])
                ->exists()
            ;

            if ($cekJadwalBentrok) {
                return ApiResponse::error('Ruang dan sesi pada tanggal ini sudah terpakai', 422);
            }

            // Cek apakah ada jadwal bentrok dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($validated['tugas_akhir_id']);
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            $cekBentrokDosen = UjianTA::where('tanggal_ujian', $validated['tanggal_ujian'])
                ->where('sesi_id', $validated['sesi_id'])
                ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                    $query->whereIn('dosen_id', $listDosenPenguji);
                })
                ->exists();

            if ($cekBentrokDosen) {
                return ApiResponse::error('Dosen penguji sudah ada yang ujian pada sesi ini', 422);
            }

            $ujianTA = UjianTA::create($validated);
            return ApiResponse::success(
                $ujianTA->load(['tugasAkhir', 'tugasAkhir.mahasiswa', 'tugasAkhir.dosenPenguji', 'ruang', 'sesi']),
                'Data ujian TA berhasil ditambahkan'
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal ditambahkan: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {

        try {


            $ujianTA = UjianTA::findOrFail($id);

            $validated = $request->validate([
                'tanggal_ujian' => 'sometimes|required|date',
                'ruang_id' => 'sometimes|required|exists:ruang,id',
                'sesi_id' => 'sometimes|required|exists:sesi,id',
                'semester_id' => 'sometimes|required|exists:semester,id',
                'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajaran,id',
                'status_ujian' => 'sometimes|required|in:aktif,non-aktif'
            ]);

            // Cek apakah ada jadwal bentrok ruangan dan sesinya
            if (isset($validated['tanggal_ujian']) || isset($validated['ruang_id']) || isset($validated['sesi_id'])) {
                $cekJadwalBentrok = UjianTA::where('id', '!=', $id)
                    ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                    ->where('ruang_id', $validated['ruang_id'] ?? $ujianTA->ruang_id)
                    ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                    ->exists();
            }

            if ($cekJadwalBentrok) {
                return ApiResponse::error('Ruang dan sesi pada tanggal ini sudah terpakai', 422);
            }

            // Cek apakah ada jadwal bentrok dosen penguji
            $tugasAkhir = TugasAkhir::with('dosenPenguji')->findOrFail($validated['tugas_akhir_id']);
            $listDosenPenguji = $tugasAkhir->dosenPenguji->pluck('dosen_id')->toArray();

            $cekBentrokDosen = UjianTA::where('id', '!=', $id)
                ->where('tanggal_ujian', $validated['tanggal_ujian'] ?? $ujianTA->tanggal_ujian)
                ->where('sesi_id', $validated['sesi_id'] ?? $ujianTA->sesi_id)
                ->whereHas('tugasAkhir.dosenPenguji', function ($query) use ($listDosenPenguji) {
                    $query->whereIn('dosen_id', $listDosenPenguji);
                })
                ->exists();

            if ($cekBentrokDosen) {
                return ApiResponse::error('Dosen penguji sudah ada yang ujian pada sesi ini', 422);
            }

            $ujianTA->update($validated);
            return ApiResponse::success(
                $ujianTA->fresh()->load(['tugasAkhir', 'tugasAkhir.mahasiswa', 'tugasAkhir.dosenPenguji', 'ruang', 'sesi']),
                'Data ujian TA berhasil diubah'
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validasi gagal: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Data ujian TA gagal diubah: ' . $e->getMessage(), 500);
        }
    }
    public function jadwalUjianForMhs()
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

            // Ambil ujian TA beserta relasi
            $ujianTA = UjianTA::with([
                'ruang:id,nama_ruang',
                'sesi:id,nama_sesi,waktu_mulai,waktu_selesai',
            ])
                ->where('tugas_akhir_id', $tugasAkhir->id)
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
                    ],
                    'jabatan' => $penguji->jabatan,
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
}