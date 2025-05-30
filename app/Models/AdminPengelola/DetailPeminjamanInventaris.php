<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjamanInventaris extends Model
{
    use HasFactory;
    protected $table = 'detail_peminjaman_inventaris';
    protected $primaryKey = 'id_detail_peminjaman';
    protected $fillable = [
        'id_peminjaman_inventaris',
        'id_inventaris',
        'catatan_pengembalian',
        'kondisi_akhir_pengembalian', // baik, rusak, hilang
        'status',
        'tanggal_kembali',

    ];

     public function peminjamanInventaris()
    {
        return $this->belongsTo(PeminjamanInventaris::class, 'id_peminjaman_inventaris', 'id_peminjaman_inventaris');
    }
    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class, 'id_inventaris', 'id_inventaris');
    }
}
