<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LantaiGedung extends Model
{
    use HasFactory;
    protected $table = 'lantai_gedung';
    protected $primaryKey = 'id_lantai';

    protected $fillable = [
        'nama_lantai',
    ];
}
