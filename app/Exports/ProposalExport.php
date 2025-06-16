<?php

namespace App\Exports;

use App\Models\Proposal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class ProposalExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
    protected $search;
    protected $statusNilai;

    public function __construct($search = null, $statusNilai = null)
    {
        $this->search = $search;
        $this->statusNilai = $statusNilai;
    }

    public function collection()
    {
        $query = Proposal::with([
            'tugasAkhir.mahasiswa:id,nim,name',
            'tugasAkhir.dosenPembimbing:id,name',
            'tugasAkhir.tahunAjaran'
        ]);

        // Filter sama seperti di tabelListSeminarProposalForAdmin
        $query->where('status_proposal', 1);

        // Filter tahun ajaran default
        $query->whereHas('tugasAkhir.tahunAjaran', function ($q) {
            $q->where('is_default', 1);
        });

        // Fitur search nim/nama mahasiswa atau judul proposal
        if ($this->search) {
            $query->whereHas('tugasAkhir.mahasiswa', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nim', 'like', '%' . $this->search . '%');
            })
                ->orWhere('judul_proposal', 'like', '%' . $this->search . '%');
        }

        // Filter status_nilai: sudah/belum
        if ($this->statusNilai) {
            if ($this->statusNilai === 'sudah') {
                $query->whereHas('tugasAkhir', function ($q) {
                    $q->whereNotNull('nilai_proposal');
                });
            } elseif ($this->statusNilai === 'belum') {
                $query->whereHas('tugasAkhir', function ($q) {
                    $q->whereNull('nilai_proposal');
                });
            }
        }

        // Urutkan sama seperti di controller
        $query->orderByRaw("CASE WHEN ( SELECT nilai_proposal FROM tugas_akhir WHERE tugas_akhir.proposal_id = proposal.id LIMIT 1 ) IS NULL THEN 0 ELSE 1 END")->orderByDesc('id');

        return $query->get()->map(function ($item) {
            return [
                'nim' => $item->tugasAkhir->mahasiswa->nim ?? '',
                'nama' => $item->tugasAkhir->mahasiswa->name ?? '',
                'judul_proposal' => $item->judul_proposal ?? '',
                'dosen_pembimbing' => $item->tugasAkhir->dosenPembimbing->name ?? '',
                'nilai_proposal' => $item->tugasAkhir->nilai_proposal ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Judul Proposal',
            'Dosen Pembimbing',
            'Nilai Proposal',
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
                $event->sheet->getStyle('A1:E1')->applyFromArray([
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