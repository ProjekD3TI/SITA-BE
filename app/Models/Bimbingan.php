<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;
    protected $table = 'bimbingan';
    protected $with = 'tugasAkhir';
    protected $fillable = [
        'tugas_akhir_id',
        'catatan',
        'bab',
        'status_verifikasi',
        'tanggal_bimbingan'
    ];

    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id', 'id');
    }

}
