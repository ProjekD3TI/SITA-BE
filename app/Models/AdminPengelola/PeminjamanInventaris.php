<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanInventaris extends Model
{
    use HasFactory;
    protected $table = 'peminjaman_inventaris';
    protected $primaryKey = 'id_peminjaman_inventaris';

    protected $fillable = [
        'id_peminjam', 
        'tipe_peminjam', //mahasiswa, dosen
        'status_peminjaman',  //Selesai,  Dipinjam, dikembalikan sebagian
        'nama_peminjam',
        'ni_peminjam',
        'no_telp', 
        'tanggal_pinjam', 
        'kegunaan', 
        'referensi', 
        'qr_code_path', 
        'created_by',
    ];

    public function detailPeminjamanInventaris()
    {
        return $this->hasMany(DetailPeminjamanInventaris::class, 'id_peminjaman_inventaris', 'id_peminjaman_inventaris');
    }
    
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
    public function inventaris() {
        return $this->belongsToMany(Inventaris::class, 'peminjaman_inventaris', 'id_peminjaman_inventaris', 'id_inventaris');
    }
}
