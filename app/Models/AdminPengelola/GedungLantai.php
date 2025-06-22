<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GedungLantai extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'gedung_lantai';
    protected $fillable = [
        'id_lantai',
        'id_gedung'

    ];

}