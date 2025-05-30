<?php

namespace App\Models\AdminPengelola;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanBhp extends Model
{
    use HasFactory;
    protected $table = 'peminjaman_bhps';
    protected $primaryKey = 'id_peminjaman_bhp';

    protected $fillable = [
        'id_bhp',
        'id_peminjam', 
        'nama_peminjam', 
        'tipe_peminjam', //mahasiswa, dosen
        'ni_peminjam', //nomor induk
        'no_telp', 
        'kegunaan', 
        'tanggal_pinjam', 
        'tanggal_kembali', 
        'jumlah_penggunaan', 
        'status_pengembalian', 
        'jumlah_kembali_bagus', 
        'jumlah_kembali_rusak', 
        'keterangan_pengembalian',
        'created_by',
    ];
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

}
