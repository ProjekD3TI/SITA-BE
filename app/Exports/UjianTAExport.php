<?php

namespace App\Exports;

use App\Models\TugasAkhir;
use App\Models\UjianTA;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class UjianTAExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
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

            // --- 1. Data UjianTA yang sudah dijadwalkan ---
            $ujianQuery = UjianTA::with([
                'tugasAkhir' => function ($q) {
                    $q->where('status_aktif', 1)
                        ->whereIn('status_valid', [1, 3])
                        ->with([
                            'mahasiswa:id,nim,name',
                            'dosenPembimbing:id,name',
                            'dosenPenguji:id,name',
                            'tahunAjaran:id,tahun_ajaran'
                        ]);
                },
                'sesi:id,waktu_mulai,waktu_selesai',
                'ruang:id,nama_ruang',
                'tahunAjaran:id,tahun_ajaran'
            ])->whereHas('tugasAkhir', function ($q) {
                $q->where('status_aktif', 1)
                    ->whereIn('status_valid', [1, 3]);
            });

            // Fitur search nim, nama, judul tugas akhir
            if ($this->search) {
                $ujianQuery->whereHas('tugasAkhir.mahasiswa', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nim', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('tugasAkhir', function ($q) {
                        $q->where('judul_tugas_akhir', 'like', '%' . $this->search . '%');
                    });
            }

            // Filter tahun ajaran pada ujian_ta
            if ($this->tahunAjaranId) {
                $ujianQuery->where('tahun_ajaran_id', $this->tahunAjaranId);
            }

            // Filter status kelulusan pada ujian_ta
            $filterMenunggu = false;
            if ($this->status) {
                $status = $this->status;
                if ($status === 'menunggu') {
                    $filterMenunggu = true;
                    $ujianQuery->whereRaw('1=0'); // Kosongkan hasil
                } elseif ($status === 'revisi_lulus' || $status === 'revisi-lulus' || $status === 'revisi') {
                    $ujianQuery->where('status_kelulusan', 3);
                } elseif (in_array($status, ['1', '2', '3', '4'])) {
                    $ujianQuery->where('status_kelulusan', $status);
                } elseif ($status === 'aktif') {
                    $ujianQuery->where('status_ujian', 'aktif');
                } elseif ($status === 'non-aktif') {
                    $ujianQuery->where('status_ujian', 'non-aktif');
                }
            }

            $ujianList = $ujianQuery->get();

            foreach ($ujianList as $item) {
                $ta = $item->tugasAkhir;
                if (!$ta)
                    continue;

                $status_kelulusan = $item->status_kelulusan;
                if (is_null($status_kelulusan)) {
                    $status_kategori = $item->status_ujian === 'aktif' ? 'Terjadwal' : 'Menunggu';
                } else {
                    switch ($status_kelulusan) {
                        case 1:
                            $status_kategori = 'Lulus Tanpa Revisi';
                            break;
                        case 2:
                            $status_kategori = 'Lulus Revisi Tanpa Diujikan';
                            break;
                        case 3:
                            $status_kategori = 'Belum Lulus Revisi Diujikan Lagi';
                            break;
                        case 4:
                            $status_kategori = 'Tidak Lulus Ulang TA';
                            break;
                        default:
                            $status_kategori = 'Tidak Diketahui';
                    }
                }

                $tanggalUjian = $item->tanggal_ujian
                    ? Carbon::parse($item->tanggal_ujian)->translatedFormat('l, d F Y')
                    : '-';

                $result->push([
                    'nim' => $ta->mahasiswa->nim ?? '-',
                    'nama' => $ta->mahasiswa->name ?? '-',
                    'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? '-',
                    'tahun_ajaran_ujian' => $item->tahunAjaran->tahun_ajaran ?? '-',
                    'tanggal_ujian' => $tanggalUjian,
                    'waktu' => ($item->sesi->waktu_mulai ?? '-') . ' - ' . ($item->sesi->waktu_selesai ?? '-'),
                    'ruang' => $item->ruang->nama_ruang ?? '-',
                    'nilai_akhir' => $item->nilai_akhir ?? '-',
                    'status_kategori' => $status_kategori,
                    'dosen_pembimbing' => $ta->dosenPembimbing->name ?? '-',
                ]);
            }

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
            if ($this->search) {
                $queryTaBelumUjian->where(function ($q) {
                    $q->whereHas('mahasiswa', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('nim', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('judul_tugas_akhir', 'like', '%' . $this->search . '%');
                });
            }

            // Filter tahun ajaran pada tugas akhir
            if ($this->tahunAjaranId) {
                $queryTaBelumUjian->where('tahun_ajaran_id', $this->tahunAjaranId);
            }

            // Filter status kelulusan pada tugas akhir
            if ($this->status) {
                $status = $this->status;
                if ($status === 'menunggu') {
                    // Tidak perlu filter tambahan
                } else {
                    $queryTaBelumUjian->whereRaw('1=0');
                }
            }

            $taBelumUjian = $queryTaBelumUjian->get();

            foreach ($taBelumUjian as $ta) {
                $result->push([
                    'nim' => $ta->mahasiswa->nim ?? '-',
                    'nama' => $ta->mahasiswa->name ?? '-',
                    'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? '-',
                    'tahun_ajaran_ujian' => $ta->tahunAjaran->tahun_ajaran ?? '-',
                    'tanggal_ujian' => '-',
                    'waktu' => '-',
                    'ruang' => '-',
                    'nilai_akhir' => '-', // Tidak ada nilai akhir untuk yang belum punya ujianTA aktif
                    'status_kategori' => 'Menunggu',
                    'dosen_pembimbing' => $ta->dosenPembimbing->name ?? '-',
                ]);
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

    public function styles(Worksheet $sheet)
    {
        // Header tebal dan rata tengah
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Header background kuning dan border
                $event->sheet->getStyle('A1:J1')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFF700'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                // Semua cell ada border
                $highestRow = $event->sheet->getHighestRow();
                $highestCol = $event->sheet->getHighestColumn();
                $event->sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                // Isi tabel (A2 dst) rata kiri
                $event->sheet->getStyle("A2:{$highestCol}{$highestRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            },
        ];
    }
}
