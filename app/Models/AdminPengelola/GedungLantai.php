<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GedungLantai extends Model
{
    use HasFactory;
    protected $table = 'gedung_lantai';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_lantai',
        'id_gedung',
    ];
}