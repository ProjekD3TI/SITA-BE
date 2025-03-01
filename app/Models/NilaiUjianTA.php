<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiUjianTA extends Model
{
    use HasFactory;
    protected $table = 'nilai_ujian_ta';
    protected $fillable = ['ujian_ta_id', 'dosen_id', 'nilai'];

    public function ujianTA()
    {
        return $this->belongsTo(UjianTA::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}
