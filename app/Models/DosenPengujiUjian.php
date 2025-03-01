<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenPengujiUjian extends Model
{
    use HasFactory;
    protected $table = 'dosen_penguji_ujian';
    protected $fillable = ['mahasiswa_id', 'dosen_id', 'jadwal_ujian_ta_id'];

}
