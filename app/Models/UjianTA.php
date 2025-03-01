<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianTA extends Model
{
    use HasFactory;
    protected $table = 'ujian_ta';
    protected $fillable = ['tugas_akhir_id', 'jadwal_ujian_ta_id'];

    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class);
    }

    public function nilaiUjianTA()
    {
        return $this->hasMany(NilaiUjianTA::class, 'ujian_ta_id', 'id');
    }

    public function jadwalUjianTA()
    {
        return $this->belongsTo(JadwalUjianTA::class);
    }
}
