<?php

namespace App\Exports;

use App\Models\AdminPengelola\PeminjamanInventaris;
use App\Models\AdminPengelola\DetailPeminjamanInventaris;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class PeminjamanInventarisExport implements FromCollection, WithHeadings
{
    protected $searchTerm;
    protected $status;
    protected $start_date;
    protected $end_date;

    public function __construct($searchTerm = null, $status = null, $start_date = null, $end_date = null)
    {
        $this->searchTerm = $searchTerm;
        $this->status = $status;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        try {
            $query = PeminjamanInventaris::with([
                'detailPeminjamanInventaris.inventaris.jenisInventaris',
                'detailPeminjamanInventaris.inventaris.jenisInventaris.merk',
                'detailPeminjamanInventaris.inventaris.jenisInventaris.tipe'
            ]);

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('referensi', 'like', "%{$this->searchTerm}%")
                      ->orWhere('nama_peminjam', 'like', "%{$this->searchTerm}%")
                      ->orWhere('ni_peminjam', 'like', "%{$this->searchTerm}%")
                      ->orWhere('kegunaan', 'like', "%{$this->searchTerm}%")
                      ->orWhere('no_telp', 'like', "%{$this->searchTerm}%");
                });
            }

            if ($this->start_date) {
                $query->whereDate('tanggal_pinjam', '>=', $this->start_date);
            }
            
            if ($this->end_date) {
                $query->whereDate('tanggal_pinjam', '<=', $this->end_date);
            }

            $query->orderBy('created_at', 'desc');
            $peminjaman_inventaris = $query->get();

            $peminjaman_inventaris = $peminjaman_inventaris->map(function($item) {
                $totalDipinjam = $item->detailPeminjamanInventaris->count();
                $totalDikembalikan = $item->detailPeminjamanInventaris->whereNotNull('tanggal_kembali')->count();
                
                $returnStatus = 'Belum Dikembalikan';
                if ($totalDikembalikan > 0) {
                    if ($totalDikembalikan >= $totalDipinjam) {
                        $returnStatus = 'Sudah Dikembalikan';
                    } else {
                        $returnStatus = 'Sebagian Dikembalikan';
                    }
                }
                
                $item->return_status = $returnStatus;
                return $item;
            });

            if ($this->status) {
                $peminjaman_inventaris = $peminjaman_inventaris->filter(function($item) {
                    return $item->return_status === $this->status;
                });
            }

            $exportData = collect();
            
            foreach ($peminjaman_inventaris as $peminjaman) {
                foreach ($peminjaman->detailPeminjamanInventaris as $detail) {
                    $inventaris = $detail->inventaris;
                    $jenisInventaris = $inventaris ? $inventaris->jenisInventaris : null;
                    $merk = $jenisInventaris && $jenisInventaris->merk ? $jenisInventaris->merk->nama_merk : null;
                    $tipe = $jenisInventaris && $jenisInventaris->tipe ? $jenisInventaris->tipe->nama_tipe : null;
                    
                    $exportData->push([
                        'Referensi' => $peminjaman->referensi,
                        'Nama Peminjam' => $peminjaman->nama_peminjam,
                        'Tipe Peminjam' => $peminjaman->tipe_peminjam,
                        'Nomor Induk' => $peminjaman->ni_peminjam,
                        'No Telepon' => $peminjaman->no_telp,
                        'Kegunaan' => $peminjaman->kegunaan,
                        'Tanggal Pinjam' => $peminjaman->tanggal_pinjam ? date('d/m/Y', strtotime($peminjaman->tanggal_pinjam)) : '',
                        'Nama Inventaris' => $jenisInventaris->nama_inventaris ?? 'N/A',
                        'Kode Inventaris' => $inventaris->no_inventaris ?? 'N/A',
                        'Merek' => $merk ?? 'N/A',
                        'Tipe' => $tipe ?? 'N/A',
                        'Tanggal Kembali' => $detail->tanggal_kembali ? date('d/m/Y', strtotime($detail->tanggal_kembali)) : 'Belum Dikembalikan',
                        'Kondisi Akhir' => $detail->kondisi_akhir_pengembalian ?? 'Belum Dikembalikan',
                        'Status Pengembalian' => $detail->tanggal_kembali ? 'Sudah Dikembalikan' : 'Belum Dikembalikan',
                        'Catatan Pengembalian' => $detail->catatan_pengembalian ?? '',
                        'Status Peminjaman Grup' => $peminjaman->return_status,
                        'Total Item Dipinjam' => $peminjaman->detailPeminjamanInventaris->count(),
                        'Total Item Dikembalikan' => $peminjaman->detailPeminjamanInventaris->whereNotNull('tanggal_kembali')->count(),
                    ]);
                }
            }

            return $exportData;

        } catch (\Exception $e) {
            \Log::error('Error exporting PeminjamanInventaris data: ' . $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'Referensi',
            'Nama Peminjam',
            'Tipe Peminjam',
            'Nomor Induk',
            'No Telepon',
            'Kegunaan',
            'Tanggal Pinjam',
            'Nama Inventaris',
            'Kode Inventaris',
            'Merek',
            'Tipe',
            'Tanggal Kembali',
            'Kondisi Akhir',
            'Status Pengembalian',
            'Catatan Pengembalian',
            'Status Peminjaman Grup',
            'Total Item Dipinjam',
            'Total Item Dikembalikan',
        ];
    }
}