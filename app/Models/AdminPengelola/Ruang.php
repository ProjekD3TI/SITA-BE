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
        'id_lantai',
        'status',
    ];
    public function fotos() {
        return $this->hasMany(Foto::class, 'id_ruang');
    }
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class, 'id_ruang');
    }
    public function gedung()
    {
        return $this->belongsTo(Gedung::class, 'id_gedung', 'id_gedung');
    }
    
    public function lantaiGedung()
    {
        return $this->belongsTo(LantaiGedung::class, 'id_lantai', 'id_lantai');
    }
    public function lantai()
{
    return $this->belongsTo(LantaiGedung::class, 'id_lantai');
}

}