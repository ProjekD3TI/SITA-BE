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
        'umur_pelapor',      // Added field for age range
        'profesi',           // Added field for profession
        'jenis_kelamin',     // Added field for gender
        'bukti_pelanggaran', // Added field for evidence
        'terlapor',          // Added field for reported person
        'saksi',             // Changed to JSON field
        'lampiran_link',
    ];

    protected $casts = [
        'image_path' => 'array', // Cast as array if you're storing multiple images
        'bukti_pelanggaran' => 'array', // Cast JSON to array
        'terlapor' => 'array', // Cast JSON to array
        'saksi' => 'array', // Cast JSON to array
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