<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianTA extends Model
{
    use HasFactory;
    protected $table = 'ujian_ta';
    protected $fillable = [
        'tanggal_ujian',
        'ruang_id',
        'sesi_id',
        'semester_id',
        'tahun_ajaran_id',
        'status_ujian',
    ];
}
