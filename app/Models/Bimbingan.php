<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;
    protected $table = 'bimbingan';
    protected $fillable = [
        'tugas_akhir_id',
        'catatan',
        'status_verifikasi',
    ];
}
