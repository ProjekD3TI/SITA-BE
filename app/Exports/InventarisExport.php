<?php

namespace App\Exports;

use App\Models\AdminPengelola\Inventaris;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventarisExport implements FromCollection, WithHeadings
{
    protected $searchTerm;
    protected $status;
    protected $kondisi;

    public function __construct($searchTerm = null, $status = null, $kondisi = null)
    {
        $this->searchTerm = $searchTerm;
        $this->status = $status;
        $this->kondisi = $kondisi;
    }

    public function collection()
    {
        try {
            $query = Inventaris::query()
                ->leftJoin('jenis_inventaris', 'inventaris.id_jenis_inventaris', '=', 'jenis_inventaris.id_jenis_inventaris')
                ->leftJoin('merk', 'jenis_inventaris.id_merk', '=', 'merk.id_merk') 
                ->leftJoin('tipe', 'jenis_inventaris.id_tipe', '=', 'tipe.id_tipe')
                ->leftJoin('ruangs', 'inventaris.id_ruang', '=', 'ruangs.id_ruang')
                ->select(
                    'inventaris.*',
                    'jenis_inventaris.nama_inventaris',
                    'merk.nama_merk', 
                    'tipe.nama_tipe',
                    'ruangs.nama_ruang'
                );

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('inventaris.no_inventaris', 'like', "%{$this->searchTerm}%")
                        ->orWhere('jenis_inventaris.nama_inventaris', 'like', "%{$this->searchTerm}%")
                        ->orWhere('ruangs.nama_ruang', 'like', "%{$this->searchTerm}%");
                });
            }

            if ($this->status !== null) {
                $query->where('inventaris.status', $this->status);
            }

            if ($this->kondisi !== null) {
                $query->where('inventaris.kondisi', $this->kondisi);
            }

            $query->orderBy('inventaris.created_at', 'desc');
            $inventaris = $query->get();

            return $inventaris->map(function ($item) {
                return [
                    'No Inventaris' => $item->no_inventaris,
                    'Nama Inventaris' => $item->nama_inventaris,
                    'Merk' => $item->nama_merk,
                    'Tipe' => $item->nama_tipe,
                    'Lokasi' => $item->nama_ruang,
                    'Kondisi' => $item->kondisi,
                    'Status' => $item->status ? 'Bisa Digunakan' : 'Dipinjam',
                    'Tahun Pengadaan' => $item->tahun_pengadaan,
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Error exporting Inventaris data: ' . $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'No Inventaris',
            'Nama Inventaris',
            'Merk',
            'Tipe',
            'Lokasi',
            'Kondisi',
            'Status',
            'Tahun Pengadaan',
        ];
    }
}