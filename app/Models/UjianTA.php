<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianTA extends Model
{
    use HasFactory;
    protected $table = 'ujian_ta';
    protected $fillable = ['id_tugas_akhir', 'id_jadwal_ujian_ta'];
}
