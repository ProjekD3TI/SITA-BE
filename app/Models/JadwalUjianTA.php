<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUjianTA extends Model
{
    use HasFactory;
    protected $table = 'jadwal_ujian_ta';
    protected $fillable = ['ruang_id', 'sesi_id', 'tanggal'];


    public function UjianTA()
    {
        return $this->hasOne(UjianTA::class, 'jadwal_ujian_ta_id', 'id');
    }

    public function sesi()
    {
        return $this->belongsTo(Sesi::class);
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }
}
