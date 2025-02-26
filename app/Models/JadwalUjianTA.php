<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUjianTA extends Model
{
    use HasFactory;
    protected $table = 'jadwal_ujian_ta';
    protected $fillable = ['id_ruang', 'id_sesi', 'tanggal'];
}
