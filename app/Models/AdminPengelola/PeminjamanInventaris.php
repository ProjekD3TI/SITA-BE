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
        'id_inventaris', 
        'id_peminjam', 
        'tipe_peminjam', //mahasiswa, dosen
        'kegunaan', 
        'status_peminjaman',  //sedang dipinjam, sudah dikembalikan
        'nama_peminjam',
        'ni_peminjam',
        'no_telp', 
        'tanggal_pinjam', 
        'tanggal_kembali',  
        'catatan_pengembalian',  
        'kondisi_akhir_pengembalian',  // baik, rusak, hilang
        'referensi', 
        // 'penanggung_jawab', 
        // 'no_reservasi',
    ];
    public static function generateNoReservasi() {
        $terbaru = self::orderBy('created_at', 'desc')->first();
        $nextNo = $terbaru ? intval(substr($terbaru->no_reservasi, 5)) + 1 : 1;
    
        do {
            $noReservasi = 'RSVPA' . str_pad($nextNo, 5, '0', STR_PAD_LEFT);
            $exists = self::where('no_reservasi', $noReservasi)->exists();
            $nextNo++;
        } while ($exists);
    
        return $noReservasi;
    }
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
    public function inventaris() {
        return $this->belongsToMany(Inventaris::class, 'peminjaman_inventaris', 'id_peminjaman_inventaris', 'id_inventaris');
    }
}
