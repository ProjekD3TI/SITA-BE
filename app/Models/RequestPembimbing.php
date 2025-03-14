<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPembimbing extends Model
{
    use HasFactory;
    protected $table = 'request_pembimbing';
    protected $fillable = [
        'tugas_akhir_id',
        'dosen_id',
        'status_request',
        'tanggal_request',
        'tanggal_respon',
    ];
}
