<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    use HasFactory;
    protected $table = 'inventaris';
    protected $primaryKey = 'id_inventaris';

    protected $fillable = [
        'id_jenis_inventaris',
        'no_inventaris', 
        'kondisi',
        'status', //dipinjam atau tdk
        'id_ruang',
        'tahun_pengadaan',
        'qr_code_path',
    ];
    public function jenisInventaris()
    {
        return $this->belongsTo(JenisInventaris::class, 'id_jenis_inventaris');
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'id_ruang');
    }
    public function peminjaman() {
        return $this->belongsToMany(PeminjamanInventaris::class, 'peminjaman_inventaris', 'id_inventaris', 'id_peminjaman_inventaris');
    }
}
