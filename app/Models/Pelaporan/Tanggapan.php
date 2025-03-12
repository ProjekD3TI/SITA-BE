<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tanggapan extends Model
{
    use HasFactory;
    protected $table = 'tanggapan';
    protected $primaryKey = 'tanggapan_id';

    protected $fillable = [
        'text', 
        'user_id',
    ];

}
