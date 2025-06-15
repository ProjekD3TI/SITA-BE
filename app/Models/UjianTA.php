<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianTA extends Model
{
    use HasFactory;
    protected $table = 'ujian_ta';
    // protected $with = ['tugasAkhir', 'tahunAjaran', 'semester', 'ruang', 'sesi'];
    protected $fillable = [
        'tugas_akhir_id',
        'tanggal_ujian',
        'ruang_id',
        'sesi_id',
        'semester_id',
        'tahun_ajaran_id',
        'status_kelulusan',
        'status_ujian',
    ];

    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id', 'id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id', 'id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id', 'id');
    }

    public function sesi()
    {
        return $this->belongsTo(Sesi::class, 'sesi_id', 'id');
    }

}
