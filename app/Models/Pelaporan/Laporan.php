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

    protected $casts = [
        'image_path' => 'array', // This will automatically cast the JSON string to an array
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

    /**
     * Get the image URLs for display
     * 
     * @return array
     */
    public function getImageUrls()
    {
        if (is_string($this->image_path) && $this->isJson($this->image_path)) {
            return json_decode($this->image_path, true);
        } elseif (is_array($this->image_path)) {
            return $this->image_path;
        }
        
        // Fallback for single image path stored as string
        return [$this->image_path];
    }

    /**
     * Check if a string is valid JSON
     * 
     * @param string $string
     * @return boolean
     */
    protected function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}