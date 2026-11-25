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
            $statuses = ($this->status) ? explode(',', $this->status) : [];

            // --- 1. Data UjianTA yang sudah dijadwalkan ---
            if (empty($statuses) || count(array_diff($statuses, ['menunggu'])) > 0) {
                $ujianQuery = UjianTA::with([
                    'tugasAkhir' => function ($q) {
                        $q
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
                ]);
                // ->whereHas('tugasAkhir', function ($q) {
                //     $q->where('status_aktif', 1)
                //         ->whereIn('status_valid', [1, 3]);
                // });

                if ($this->search) {
                    $ujianQuery->where(function ($mainQuery) {
                        $mainQuery->whereHas('tugasAkhir.mahasiswa', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('nim', 'like', '%' . $this->search . '%');
                        })
                            ->orWhereHas('tugasAkhir', function ($q) {
                                $q->where('judul_tugas_akhir', 'like', '%' . $this->search . '%');
                            });
                    });
                }

                if ($this->tahunAjaranId) {
                    $tahunAjaranList = explode(',', $this->tahunAjaranId);
                    $tahunAjaranList = array_map('trim', $tahunAjaranList);
                    $ujianQuery->whereIn('tahun_ajaran_id', $tahunAjaranList);
                }

                if (!empty($statuses)) {
                    $ujianQuery->where(function ($q) use ($statuses) {
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

                $ujianList = $ujianQuery->get();

                foreach ($ujianList as $item) {
                    $ta = $item->tugasAkhir;
                    if (!$ta)
                        continue;
                    $result->push($this->mapUjianData($item, $ta));
                }
            }

            // --- 2. Data TugasAkhir yang belum punya UjianTA aktif ---
            if (empty($statuses) || in_array('menunggu', $statuses)) {
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

                if ($this->search) {
                    $queryTaBelumUjian->where(function ($q) {
                        $q->whereHas('mahasiswa', function ($q2) {
                            $q2->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('nim', 'like', '%' . $this->search . '%');
                        })
                            ->orWhere('judul_tugas_akhir', 'like', '%' . $this->search . '%');
                    });
                }

                if ($this->tahunAjaranId) {
                    $tahunAjaranList = explode(',', $this->tahunAjaranId);
                    $tahunAjaranList = array_map('trim', $tahunAjaranList);
                    $queryTaBelumUjian->whereIn('tahun_ajaran_id', $tahunAjaranList);
                }

                $taBelumUjian = $queryTaBelumUjian->get();

                foreach ($taBelumUjian as $ta) {
                    $result->push($this->mapTugasAkhirData($ta));
                }
            }

            return $result;
        } catch (\Exception $e) {
            return collect([['error' => 'Terjadi kesalahan saat export data: ' . $e->getMessage()]]);
        }
    }

    private function getStatusKategori($status_kelulusan, $status_ujian)
    {
        if (is_null($status_kelulusan)) {
            return $status_ujian === 'aktif' ? 'Terjadwal' : 'Menunggu';
        }
        switch ($status_kelulusan) {
            case 1:
                return 'Lulus Tanpa Revisi';
            case 2:
                return 'Lulus Revisi Tanpa Diujikan';
            case 3:
                return 'Belum Lulus Revisi Diujikan Lagi';
            case 4:
                return 'Tidak Lulus Ulang TA';
            default:
                return 'Tidak Diketahui';
        }
    }

    private function mapUjianData($item, $ta)
    {
        $status_kategori = $this->getStatusKategori($item->status_kelulusan, $item->status_ujian);
        $tanggalUjian = $item->tanggal_ujian ? Carbon::parse($item->tanggal_ujian)->locale('id')->translatedFormat('l, d F Y') : '-';
        return [
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
        ];
    }

    private function mapTugasAkhirData($ta)
    {
        return [
            'nim' => $ta->mahasiswa->nim ?? '-',
            'nama' => $ta->mahasiswa->name ?? '-',
            'judul_tugas_akhir' => $ta->judul_tugas_akhir ?? '-',
            'tahun_ajaran_ujian' => $ta->tahunAjaran->tahun_ajaran ?? '-',
            'tanggal_ujian' => '-',
            'waktu' => '-',
            'ruang' => '-',
            'nilai_akhir' => '-',
            'status_kategori' => 'Menunggu',
            'dosen_pembimbing' => $ta->dosenPembimbing->name ?? '-',
        ];
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
