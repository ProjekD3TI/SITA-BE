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
        'semester_id',
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

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id', 'id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'id');
    }

    public function bimbingan()
    {
        return $this->hasMany(Bimbingan::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id', ownerKey: 'id');
    }

    public function ujianTA()
    {
        return $this->hasMany(UjianTA::class);
    }

    public function dosenPenguji()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_penguji', 'tugas_akhir_id', 'dosen_id')
            ->using(DosenPenguji::class)
            ->withPivot([
                'revisi',
                'catatan_semhas',
                'nilai_ujian_ta'
            ])->withTimestamps();
    }

    public function requestPembimbing()
    {
        return $this->hasOne(RequestPembimbing::class);
    }
}
