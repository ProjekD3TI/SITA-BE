<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminPengelola\Foto;

class Ruang extends Model
{
    use HasFactory;
    protected $table = 'ruangs';
    protected $primaryKey = 'id_ruang';

    protected $fillable = [
        'nama_ruang', 
        'kapasitas', 
        'fasilitas',
        'dimensi',
        'id_gedung',
        'status',
    ];
    public function fotos() {
        return $this->hasMany(Foto::class, 'id_ruang');
    }
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class, 'id_ruang');
    }
}