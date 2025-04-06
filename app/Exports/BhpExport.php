<?php
namespace App\Exports;

use App\Models\AdminPengelola\Bhp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class BhpExport implements FromCollection, WithHeadings
{
    protected $searchTerm;

    public function __construct($searchTerm = null)
    {
        $this->searchTerm = $searchTerm;
    }

    public function collection()
    {
        try {
            $query = Bhp::query();

            if ($this->searchTerm) {
                $query->where(function($q) {
                    $q->where('nama_bhp', 'like', "%{$this->searchTerm}%")
                      ->orWhere('peruntukan', 'like', "%{$this->searchTerm}%");
                });
            }
            
            $query->orderBy('created_at', 'desc');
            $bhps = $query->get();

            return $bhps->map(function ($bhp) {
                return [
                    'Nama BHP' => $bhp->nama_bhp,
                    'Kondisi Bagus' => $bhp->kondisi_bagus,
                    'Kondisi Rusak' => $bhp->kondisi_rusak,
                    'Jumlah' => $bhp->kondisi_bagus + $bhp->kondisi_rusak,
                    'Peruntukan' => $bhp->peruntukan,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error exporting BHP data: ' . $e->getMessage());
            return collect(); 
        }
    }

    public function headings(): array
    {
        return [
            'Nama BHP',
            'Kondisi Bagus',
            'Kondisi Rusak',
            'Jumlah',
            'Peruntukan',
        ];
    }
}