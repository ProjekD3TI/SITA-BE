<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiUjianTA extends Model
{
    use HasFactory;
    protected $table = 'nilai_ujian_ta';
    protected $fillable = ['id_ujian_ta', 'nip', 'nilai'];
}
