<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAkses extends Model
{
    protected $table = 'log_akses';

    protected $fillable = [
        'nama',
        'status',
        'ruang_id',
        'waktu',
    ];

    // Jika kamu menggunakan kolom created_at dan updated_at standar,
    // tidak perlu override $timestamps karena defaultnya true.
    public $timestamps = true;

    // Relasi ke tabel ruang
    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id', 'id_ruang');
    }
}
