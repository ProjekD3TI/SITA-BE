<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiBimbingan extends Model
{
    use HasFactory;

    protected $table = 'nilai_bimbingan';
    protected $fillable = [
        'nilai_akhir_bimbingan',
        'nilai_disiplin',
        'nilai_komunikasi',
        'nilai_skill_pengetahuan',
        'nilai_tingkah_laku',
        'catatan_akhir_bimbingan',
    ];

    public function tugasAkhir()
    {
        return $this->hasOne(TugasAkhir::class, 'id');
    }
}
