<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasAkhir extends Model
{
    use HasFactory;
    protected $table = "tugas_akhir";
    protected $fillable = [
        'nim',
        'judul_ta',
        'krs',
        'persetujuan_pembimbing',
        'sertifikat_kompetensi',
        'toeic',
        'berita_acara_semhas',
        'naskah_ta',
        'masa_perpanjangan_studi'
    ];
}
