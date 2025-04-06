<?php

namespace App\Exports;

use App\Models\AdminPengelola\JenisInventaris;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JenisInventarisExport implements FromCollection, WithHeadings
{
    protected $searchTerm;
    protected $statusImport;
    protected $peruntukan;

    public function __construct($searchTerm = null, $statusImport = null, $peruntukan = null)
    {
        $this->searchTerm = $searchTerm;
        $this->statusImport = $statusImport;
        $this->peruntukan = $peruntukan;
    }

    public function collection()
    {
        try {
            $query = JenisInventaris::query()
                ->leftJoin('merk', 'jenis_inventaris.id_merk', '=', 'merk.id_merk')
                ->leftJoin('tipe', 'jenis_inventaris.id_tipe', '=', 'tipe.id_tipe')
                ->select(
                    'jenis_inventaris.*',
                    'merk.nama_merk',
                    'tipe.nama_tipe'
                );

            if ($this->searchTerm) {
                $query->where(function ($q) {
                    $q->where('jenis_inventaris.nama_inventaris', 'like', "%{$this->searchTerm}%")
                        ->orWhere('merk.nama_merk', 'like', "%{$this->searchTerm}%")
                        ->orWhere('tipe.nama_tipe', 'like', "%{$this->searchTerm}%")
                        ->orWhere('jenis_inventaris.peruntukan', 'like', "%{$this->searchTerm}%");
                });
            }

            if ($this->statusImport !== null) {
                $query->where('status_import', $this->statusImport);
            }

            if ($this->peruntukan !== null) {
                $query->where('peruntukan', $this->peruntukan);
            }

            $query->orderBy('created_at', 'desc');
            $jenisInventaris = $query->get();

            return $jenisInventaris->map(function ($item) {
                return [
                    'Nama Inventaris' => $item->nama_inventaris,
                    'Merk' => $item->nama_merk,
                    'Tipe' => $item->nama_tipe,
                    'Peruntukan' => $this->formatPeruntukan($item->peruntukan),
                    'Status Import' => $this->formatStatusImport($item->status_import),
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Error exporting Jenis Inventaris data: ' . $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'Nama Inventaris',
            'Merk',
            'Tipe',
            'Peruntukan',
            'Status Import',
        ];
    }

    private function formatPeruntukan($peruntukan)
    {
        $peruntukanMap = [
            'alat_lab' => 'Alat Laboratorium',
            'inventaris_kantor' => 'Inventaris Kantor'
        ];
    
        return $peruntukanMap[$peruntukan] ?? ucwords(str_replace('_', ' ', $peruntukan));
    }
    
    private function formatStatusImport($status)
    {
        $statusMap = [
            'produk_luar_negeri' => 'Produk Luar Negeri',
            'produk_dalam_negeri' => 'Produk Dalam Negeri'
        ];
    
        return $statusMap[$status] ?? ucwords(str_replace('_', ' ', $status));
    }
}