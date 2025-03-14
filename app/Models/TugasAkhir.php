<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasAkhir extends Model
{
    use HasFactory;
    protected $table = "tugas_akhir";
    protected $fillable = [
        'mahasiswa_id',
        'proposal_id',
        'tahun_ajaran_id',
        'judul_tugas_akhir',
        'krs',
        'persetujuan_pembimbing',
        'sertifikat_kompetensi',
        'toeic',
        'berita_acara_semhas',
        'naskah_ta',
        'masa_perpanjangan_studi',
        'nilai_proposal',
        'nilai_bimbingan',
        'catatan_proposal',
        'file_transkrip',
        'status_valid',
        'status_aktif'
    ];

}
