<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisasiRuang extends Model
{
    use HasFactory;
    protected $table = 'inventarisasi_ruang';
    protected $primaryKey = 'id_inventarisasi_ruang';
    protected $fillable = [
        'id_ruang',
        'id_inventaris'
    ];
}
