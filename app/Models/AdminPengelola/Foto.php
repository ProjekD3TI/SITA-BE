<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'fotos';  // Nama tabel 'fotos'
    protected $primaryKey = 'foto_id';  // Primary key 'foto_id'

    protected $fillable = [
        'id_ruang',   // Foreign key untuk menghubungkan dengan ruang
        'foto_path',  // Path lokasi file foto
    ];


}
