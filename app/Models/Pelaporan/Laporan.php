<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;
    protected $table = 'laporan';
    protected $primaryKey = 'laporan_id';

    protected $fillable = [
        'judul', 
        'image_path',
        'category_id',
        'deskripsi',
        'tanggal_kejadian',
        'nomor_telepon',
        'nomor_laporan',
        'nama_pelapor',
        'ni_pelapor',
        'status',
        'tanggapan',
        'email',
    ];

    public static function generateNomorLaporan()
    {
        $prefix = 'LPR-D3TI-';
        
        $latestReport = self::orderBy('nomor_laporan', 'desc')
            ->where('nomor_laporan', 'like', $prefix . '%')
            ->first();

        if ($latestReport) {
            // ++ dri terakhir
            $counter = (int) substr($latestReport->nomor_laporan, -5);
            $counter++;
        } else {
            $counter = 1;
        }

        $formattedCounter = str_pad($counter, 5, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedCounter;
    }
}

