<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiUjianTA extends Model
{
    use HasFactory;

    protected $table = 'nilai_ujian_ta';
    protected $fillable = [
        'kualitas_tata_laporan',
        'kemampuan_teknis',
        'kualitas_produk',
        'presentasi',
        'rerata_ujian_ta',
    ];

    public function dosenPenguji()
    {
        return $this->hasOne(DosenPenguji::class, 'nilai_ujian_ta_id', 'id');
    }

}
