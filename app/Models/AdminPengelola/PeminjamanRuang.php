<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanRuang extends Model
{
    use HasFactory;
    protected $table = 'peminjaman_ruangs';
    protected $primaryKey = 'id_peminjaman_ruang';

    protected $fillable = [
        'id_ruang', 'id_peminjam', 'id_periode', 'id_jenis_acara', 'id_sesi_mulai', 'id_sesi_selesai', 'kepentingan', 'surat', 'status_peminjaman', 'status_pengembalian',
        'no_telepon', 'nama_peminjam', 'nim_peminjam', 'tanggal_peminjaman', 'penanggung_jawab', 'no_reservasi',
    ];

    public static function generateNoReservasi()
    {
        $today = now();
        $prefix = 'RSVPR';
        $yearMonth = $today->format('Ym');
        
        $lastNumber = self::where('no_reservasi', 'like', $prefix . '-' . $yearMonth . '%')
        ->max('no_reservasi');
        
        if ($lastNumber) {
            // Mengambil 4 digit terakhir dan menambahkan 1
            $sequence = (int) substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }
        
        // Output = RSV-YYYYMM-XXXX (contoh: RSV-202401-0001)
        return $prefix . '-' . $yearMonth . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}

