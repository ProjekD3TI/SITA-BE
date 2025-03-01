<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'sesi';
    protected $fillable = ['waktu_mulai', 'waktu_selesai'];

    public function jadwalUjianTA()
    {
        return $this->hasMany(JadwalUjianTA::class, 'sesi_id', 'id');
    }
}
