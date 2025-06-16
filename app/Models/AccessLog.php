<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'rfid_code',
        'access_name',
        'id_ruang',
        'id_dosen',
        'id_mahasiswa',
        'role',
        'access_status',
        'reason',
        'access_time'
    ];

    public function ruang()     { return $this->belongsTo(Ruang::class, 'id_ruang'); }
    public function dosen()     { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
}