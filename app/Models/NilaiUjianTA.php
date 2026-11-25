<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiUjianTA extends Model
{
    use HasFactory;

    protected $table = 'nilai_ujian_ta';
    protected $fillable = [
        'ujian_ta_id',
        'dosen_id',
        'kualitas_tata_laporan',
        'kemampuan_teknis',
        'kualitas_produk',
        'presentasi',
        'rerata_ujian_ta',
        'revisi',
    ];

    public function ujianTA()
    {
        return $this->belongsTo(UjianTA::class, 'ujian_ta_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}