<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    use HasFactory;
    protected $table = 'gedung';
    protected $primaryKey = 'id_gedung';

    protected $fillable = [
        'nama_gedung',
        'lokasi',
        // 'id_lantai',
    ];
    public function lantais()
    {
        return $this->hasMany(LantaiGedung::class, 'id_gedung');
    }
    public function ruangs()
    {
        return $this->hasMany(Ruang::class, 'id_gedung', 'id_gedung');
    }
}