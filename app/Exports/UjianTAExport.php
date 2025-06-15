<?php

namespace App\Exports;

use App\Models\TugasAkhir;
use App\Models\UjianTA;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class UjianTAExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $status;
    protected $tahunAjaranId;

    public function __construct($search = null, $status = null, $tahunAjaranId = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->tahunAjaranId = $tahunAjaranId;
    }

    public function collection()
    {
        try {
            $result = collect();

            if ($this->tahunAjaranId) {
                $ujianQuery = UjianTA::where('tahun_ajaran_id', $this->tahunAjaranId)
                    ->with([
                        'tugasAkhir' => function ($q) {
                            $q->where('status_aktif', 1)
                                ->where('status_valid', 1)
                                ->with([
                                    'mahasiswa:id,nim,name',
                                    'dosenPembimbing:id,name',
                                    'tahunAjaran:id,tahun_ajaran'
                                ]);
                        },
                        'sesi:id,waktu_mulai,waktu_selesai',
                        'ruang:id,nama_ruang',
                        'tahunAjaran:id,tahun_ajaran'
                    ]);

                if ($this->status) {
                    if ($this->status === 'terjadwal') {
                        $ujianQuery->where('status_ujian', 'aktif');
                    }
                }

                if ($this->search) {
                    $ujianQuery->whereHas('tugasAkhir', function ($q) {
                        $q->where('judul_tugas_akhir', 'like', '%' . $this->search . '%')
                            ->orWhereHas('mahasiswa', function ($q2) {
                                $q2->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('nim', 'like', '%' . $this->search . '%');
                            });
                    });
                }

                $ujianList = $ujianQuery->get();

                foreach ($ujianList as $ujian) {
                    if (!$ujian->tugasAkhir)
                        continue;

                    if (
                        $this->status && in_array($this->status, ['1', '2', '3', '4']) &&
                        $ujian->tugasAkhir->status_kelulusan != $this->status
                    ) {
                        continue;
                    }

                    $status_kategori = $this->getStatusKategori($ujian->tugasAkhir, $ujian);

                    $tanggalUjian = $ujian->tanggal_ujian
                        ? Carbon::parse($ujian->tanggal_ujian)->translatedFormat('l, d F Y')
                        : '-';

                    $result->push([
                        'nim' => $ujian->tugasAkhir->mahasiswa->nim ?? '-',
                        'nama' => $ujian->tugasAkhir->mahasiswa->name ?? '-',
                        'judul_tugas_akhir' => $ujian->tugasAkhir->judul_tugas_akhir ?? '-',
                        'tahun_ajaran_ujian' => $ujian->tahunAjaran->tahun_ajaran ?? '-',
                        'tanggal_ujian' => $tanggalUjian,
                        'waktu' => ($ujian->sesi->waktu_mulai ?? '-') . ' - ' . ($ujian->sesi->waktu_selesai ?? '-'),
                        'ruang' => $ujian->ruang->nama_ruang ?? '-',
                        'nilai_akhir' => $ujian->tugasAkhir->nilai_akhir ?? '-',
                        'status_kategori' => $status_kategori,
                        'dosen_pembimbing' => $ujian->tugasAkhir->dosenPembimbing->name ?? '-',
                    ]);
                }
            } else {
                $taQuery = TugasAkhir::where('status_aktif', 1)
                    ->where('status_valid', 1)
                    ->with([
                        'mahasiswa:id,nim,name',
                        'dosenPembimbing:id,name',
                        'ujianTA' => function ($q) {
                            $q->with(['tahunAjaran', 'sesi', 'ruang']);
                        },
                        'tahunAjaran'
                    ]);

                if ($this->search) {
                    $taQuery->where(function ($q) {
                        $q->where('judul_tugas_akhir', 'like', '%' . $this->search . '%')
                            ->orWhereHas('mahasiswa', function ($q2) {
                                $q2->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('nim', 'like', '%' . $this->search . '%');
                            });
                    });
                }

                if ($this->status) {
                    if ($this->status === 'menunggu') {
                        $taQuery->whereDoesntHave('ujianTA', function ($q) {
                            $q->where('status_ujian', 'aktif');
                        });
                    } elseif ($this->status === 'terjadwal') {
                        $taQuery->whereHas('ujianTA', function ($q) {
                            $q->where('status_ujian', 'aktif');
                        })->whereNull('status_kelulusan');
                    } elseif (in_array($this->status, ['1', '2', '3', '4'])) {
                        $taQuery->where('status_kelulusan', $this->status);
                    }
                }

                $taList = $taQuery->get();

                foreach ($taList as $ta) {
                    $ujianAktif = $ta->ujianTA->where('status_ujian', 'aktif')->first();

                    $status_kategori = $this->getStatusKategori($ta, $ujianAktif);

                    if ($ujianAktif) {
                        $tanggalUjian = $ujianAktif->tanggal_ujian
                            ? Carbon::parse($ujianAktif->tanggal_ujian)->translatedFormat('l, d F Y')
                            : '-';

                        $result->push([
                            'nim' => $ta->mahasiswa->nim ?? '-',
                            'nama' => $ta->mahasiswa->name ?? '-',
                            'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? '-',
                            'tahun_ajaran_ujian' => $ujianAktif->tahunAjaran->tahun_ajaran ?? '-',
                            'tanggal_ujian' => $tanggalUjian,
                            'waktu' => ($ujianAktif->sesi->waktu_mulai ?? '-') . ' - ' . ($ujianAktif->sesi->waktu_selesai ?? '-'),
                            'ruang' => $ujianAktif->ruang->nama_ruang ?? '-',
                            'nilai_akhir' => $ta->nilai_akhir ?? '-',
                            'status_kategori' => $status_kategori,
                            'dosen_pembimbing' => $ta->dosenPembimbing->name ?? '-',
                        ]);
                    } else {
                        $result->push([
                            'nim' => $ta->mahasiswa->nim ?? '-',
                            'nama' => $ta->mahasiswa->name ?? '-',
                            'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? '-',
                            'tahun_ajaran_ujian' => '-',
                            'tanggal_ujian' => '-',
                            'waktu' => '-',
                            'ruang' => '-',
                            'nilai_akhir' => $ta->nilai_akhir ?? '-',
                            'status_kategori' => $status_kategori,
                            'dosen_pembimbing' => $ta->dosenPembimbing->name ?? '-',
                        ]);
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            return collect([
                [
                    'error' => 'Terjadi kesalahan saat export data: ' . $e->getMessage(),
                    'nim' => '-',
                    'nama' => '-',
                    'judul_tugas_akhir' => '-',
                    'tahun_ajaran_ujian' => '-',
                    'tanggal_ujian' => '-',
                    'waktu' => '-',
                    'ruang' => '-',
                    'nilai_akhir' => '-',
                    'status_kategori' => '-',
                    'dosen_pembimbing' => '-',
                ]
            ]);
        }
    }

    private function getStatusKategori($tugasAkhir, $ujian = null)
    {
        if (is_null($tugasAkhir->status_kelulusan)) {
            if ($ujian && $ujian->status_ujian === 'aktif') {
                return 'Terjadwal';
            } else {
                return 'Menunggu';
            }
        } else {
            switch ($tugasAkhir->status_kelulusan) {
                case 1:
                    return 'Lulus Tanpa Revisi';
                case 2:
                    return 'Lulus Revisi Tanpa Diujikan';
                case 3:
                    return 'Belum Lulus Revisi Diujikan Lagi';
                case 4:
                    return 'Tidak Lulus Ulang TA Diujikan Lagi';
                default:
                    return 'Tidak Diketahui';
            }
        }
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Judul Tugas Akhir',
            'Tahun Ajaran Ujian',
            'Tanggal Ujian',
            'Waktu',
            'Ruang',
            'Nilai Akhir',
            'Status',
            'Dosen Pembimbing',
        ];
    }
}
