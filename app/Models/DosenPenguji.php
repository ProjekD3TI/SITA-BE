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
        'revisi',
        'catatan_semhas',
        'nilai_ujian_ta',
    ];

    public $timestamps = true;

}
