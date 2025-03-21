<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPembimbing extends Model
{
    use HasFactory;
    protected $table = 'request_pembimbing';
    protected $with = ['tugaAkhir', 'dosen'];
    protected $fillable = [
        'tugas_akhir_id',
        'dosen_id',
        'status_request',
        'tanggal_request',
        'tanggal_respon',
    ];

    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id', 'id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'id');
    }
}
