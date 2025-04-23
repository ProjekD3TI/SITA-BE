<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKekerasan extends Model
{
    use HasFactory;
    protected $table = 'laporan_kekerasan_seksual';
    protected $primaryKey = 'laporan_kekerasan_id';

    protected $fillable = [
        'judul', 
        'image_path',
        'category_id',
        'deskripsi',
        'tanggal_kejadian',
        'nomor_telepon',
        'nomor_laporan_kekerasan',
        'nama_pelapor',
        'nim_pelapor',
        'tanggapan',
        'saksi_nama',
        'saksi_email',
        'saksi_nomor_telepon',
    ];

    public static function generateNomorLaporanKekerasan()
    {
        $prefix = 'LPRK-D3TI-';
        
        $latestReport = self::orderBy('nomor_laporan_kekerasan', 'desc')
            ->where('nomor_laporan_kekerasan', 'like', $prefix . '%')
            ->first();

        if ($latestReport) {
            // ++ dri terakhir
            $counter = (int) substr($latestReport->nomor_laporan_kekerasan, -5);
            $counter++;
        } else {
            $counter = 1;
        }

        $formattedCounter = str_pad($counter, 5, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedCounter;
    }
}
