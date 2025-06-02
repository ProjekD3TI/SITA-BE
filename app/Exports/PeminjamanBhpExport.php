<?php

namespace App\Exports;

use App\Models\AdminPengelola\PeminjamanBhp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PeminjamanBhpExport implements FromCollection, WithHeadings
{
    protected $searchTerm;
    protected $status_pengembalian;
    protected $start_date;
    protected $end_date;

    public function __construct($searchTerm = null, $status_pengembalian = null, $start_date = null, $end_date = null)
    {
        $this->searchTerm = $searchTerm;
        $this->status_pengembalian = $status_pengembalian;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        try {
            $query = PeminjamanBhp::query();

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('nama_peminjam', 'like', "%{$this->searchTerm}%")
                      ->orWhere('ni_peminjam', 'like', "%{$this->searchTerm}%")
                      ->orWhere('kegunaan', 'like', "%{$this->searchTerm}%");
                });
            }

            if ($this->status_pengembalian !== null) {
                $query->where('status_pengembalian', $this->status_pengembalian);
            }

            if ($this->start_date) {
                $query->whereDate('tanggal_pinjam', '>=', $this->start_date);
            }
            
            if ($this->end_date) {
                $query->whereDate('tanggal_pinjam', '<=', $this->end_date);
            }

            $query->orderBy('created_at', 'desc');
            $peminjaman_bhp = $query->get();


            return $peminjaman_bhp->map(function ($item) {
                return [
                    'Nama Peminjam' => $item->nama_peminjam,
                    'Tipe Peminjam' => $item->tipe_peminjam,
                    'Nomor Induk' => $item->ni_peminjam,
                    'No Telepon' => $item->no_telp,
                    'Kegunaan' => $item->kegunaan,
                    'Tanggal Pinjam' => $item->tanggal_pinjam ? date('d/m/Y', strtotime($item->tanggal_pinjam)) : '',
                    'Tanggal Kembali' => $item->tanggal_kembali ? date('d/m/Y', strtotime($item->tanggal_kembali)) : '',
                    'Jumlah Penggunaan' => $item->jumlah_penggunaan,
                    'Status Pengembalian' => $item->status_pengembalian ? 'Sudah Dikembalikan' : 'Belum Dikembalikan',
                    'Jumlah Kembali Bagus' => $item->jumlah_kembali_bagus ?? 0,
                    'Jumlah Kembali Rusak' => $item->jumlah_kembali_rusak ?? 0,
                    'Keterangan' => $item->keterangan_pengembalian,
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Error exporting PeminjamanBhp data: ' . $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'Nama Peminjam',
            'Tipe Peminjam',
            'Nomor Induk',
            'No Telepon',
            'Kegunaan',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Jumlah Penggunaan',
            'Status Pengembalian',
            'Jumlah Kembali Bagus',
            'Jumlah Kembali Rusak',
            'Keterangan',
        ];
    }
}
