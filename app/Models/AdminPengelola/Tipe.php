<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipe extends Model
{
    use HasFactory;
    protected $table = 'tipe';
    protected $primaryKey = 'id_tipe';

    protected $fillable = [
        'nama_tipe',
    ];
}
