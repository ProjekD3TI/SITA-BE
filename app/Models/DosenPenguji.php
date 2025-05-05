<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DosenPenguji extends Pivot
{
    use HasFactory;

    protected $table = 'dosen_penguji';
    protected $fillable = [
        'dosen_id',
        'tugas_akhir_id',
        'nilai_ujian_ta_id',
        'revisi',
        'catatan_semhas',
    ];

    public $timestamps = true;

    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id', 'id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'id');
    }

    public function nilaiUjianTA()
    {
        return $this->belongsTo(NilaiUjianTA::class, 'nilai_ujian_ta_id', 'id');
    }
}
