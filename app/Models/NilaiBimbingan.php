<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiBimbingan extends Model
{
    use HasFactory;

    protected $table = 'nilai_bimbingan';
    protected $fillable = [
        'rerata_bimbingan',
        'disiplin',
        'komunikasi',
        'skill_pengetahuan',
        'tingkah_laku',
        'catatan_akhir_bimbingan'
    ];

    public function tugasAkhir()
    {
        return $this->hasOne(TugasAkhir::class);
    }
}
