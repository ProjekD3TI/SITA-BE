<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'rfid_code',
        'id_ruang',
        'id_dosen',
        'id_mahasiswa',
        'role',
        'access_status',
        'reason',
        'access_time'
    ];

    public function ruang() {
        return $this->belongsTo(\App\Models\AdminPengelola\Ruang::class, 'id_ruang');
    }
    
    public function dosen() {
        return $this->belongsTo(\App\Models\Dosen::class, 'id_dosen');
    }
    public function mahasiswa() {
        return $this->belongsTo(\App\Models\User::class, 'id_mahasiswa'); // asumsi model User untuk mahasiswa
    }
    
}