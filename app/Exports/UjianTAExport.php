<?php

namespace App\Exports;

use App\Models\TugasAkhir;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UjianTAExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $status;

    public function __construct($search = null, $status = null)
    {
        $this->search = $search;
        $this->status = $status;
    }

    public function collection()
    {
        $query = TugasAkhir::with(['mahasiswa', 'tahunAjaran', 'ujianTA'])
            ->where('status_aktif', 1)
            ->where('status_valid', 1) // Hanya TugasAkhir yang valid
            ->whereHas('tahunAjaran', function ($q) {
                $q->where('is_default', 1);
            });
            // ->whereHas('ujianTA', function ($q) {
            //     $q->where('status_aktif', 1); // Hanya TugasAkhir yang punya ujianTA aktif
            // });

        // Filter search nim/nama mahasiswa atau judul tugas akhir
        if ($this->search) {
            $query->whereHas('mahasiswa', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nim', 'like', '%' . $this->search . '%');
            })
                ->orWhere('judul_tugas_akhir', 'like', '%' . $this->search . '%');
        }

        // Filter status sesuai dengan tabel penjadwalan
        if ($this->status) {
            switch ($this->status) {
                case 'terjadwal':
                    $query->whereHas('ujianTA');
                    break;
                case 'menunggu':
                    $query->whereDoesntHave('ujianTA');
                    break;
                case '1': // Lulus
                    $query->where('status_kelulusan', 1);
                    break;
                case '2': // Lulus Revisi
                    $query->where('status_kelulusan', 2);
                    break;
                case '3': // Revisi Ulang
                    $query->where('status_kelulusan', 3);
                    break;
                case '4': // Tidak Lulus
                    $query->where('status_kelulusan', 4);
                    break;
            }
        }

        $tugasAkhirList = $query->get();
        $result = collect();

        foreach ($tugasAkhirList as $item) {
            // Jika ada ujian TA, tampilkan setiap ujian sebagai baris terpisah
            if ($item->ujianTA->isNotEmpty()) {
                foreach ($item->ujianTA as $ujian) {
                    // Mapping status kelulusan berdasarkan ujian terakhir
                    $statusKelulusan = '';
                    switch ($item->status_kelulusan) {
                        case 1:
                            $statusKelulusan = 'Lulus';
                            break;
                        case 2:
                            $statusKelulusan = 'Lulus Revisi';
                            break;
                        case 3:
                            $statusKelulusan = 'Revisi Ulang';
                            break;
                        case 4:
                            $statusKelulusan = 'Tidak Lulus';
                            break;
                        default:
                            $statusKelulusan = 'Terjadwal';
                            break;
                    }

                    $result->push([
                        'nim' => $item->mahasiswa->nim ?? '',
                        'nama' => $item->mahasiswa->name ?? '',
                        'judul_tugas_akhir' => $item->judul_tugas_akhir ?? '',
                        'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? '',
                        'tanggal_ujian' => $ujian->tanggal_ujian ?? '-',
                        'ujian_ke' => $item->ujianTA->search($ujian) + 1, // Ujian ke berapa
                        'nilai_akhir' => $item->nilai_akhir ?? '-',
                        'status' => $statusKelulusan,
                    ]);
                }
            } else {
                // Jika belum ada ujian TA
                $result->push([
                    'nim' => $item->mahasiswa->nim ?? '',
                    'nama' => $item->mahasiswa->name ?? '',
                    'judul_tugas_akhir' => $item->judul_tugas_akhir ?? '',
                    'tahun_ajaran' => $item->tahunAjaran->tahun_ajaran ?? '',
                    'tanggal_ujian' => '-',
                    'ujian_ke' => '-',
                    'nilai_akhir' => '-',
                    'status' => 'Menunggu',
                ]);
            }
        }

        return $result;
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Judul Tugas Akhir',
            'Tahun Ajaran',
            'Tanggal Ujian',
            'Ujian Ke',
            'Nilai Akhir',
            'Status',
        ];
    }
}
