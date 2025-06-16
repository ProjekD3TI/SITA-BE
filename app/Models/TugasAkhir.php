<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasAkhir extends Model
{
    use HasFactory;
    protected $table = "tugas_akhir";
    // protected $with = ['mahasiswa', 'dosenPembimbing', 'proposal', 'semester', 'tahunAjaran', 'ujianTA', 'dosenPenguji', 'requestPembimbing'];
    protected $fillable = [
        'mahasiswa_id',
        'proposal_id',
        'semester_id',
        'dosen_pembimbing_id',
        'tahun_ajaran_id',
        'nilai_bimbingan_id',
        'kategori_id',
        'judul_tugas_akhir',
        'catatan_proposal',
        'catatan_perbaikan',
        'nilai_proposal',
        'nilai_akhir',
        'status_kelulusan',
        'status_valid',
        'status_aktif'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id', 'id');
    }

    public function dosenPembimbing()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id', 'id');
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

    public function nilaiBimbingan()
    {
        return $this->belongsTo(NilaiBimbingan::class, 'nilai_bimbingan_id', 'id');
    }
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    public function dosenPenguji()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_penguji', 'tugas_akhir_id', 'dosen_id')
            ->using(DosenPenguji::class)
            ->withPivot([
                // 'revisi',
                'catatan_semhas',
                // 'nilai_ujian_ta_id',
                'jabatan_penguji'
            ])->withTimestamps();
    }

    public function requestPembimbing()
    {
        return $this->hasOne(RequestPembimbing::class);
    }

    public function berkas()
    {
        return $this->morphMany(Berkas::class, 'berkasable');
    }
}
