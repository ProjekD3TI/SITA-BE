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
        'id_gedung',
    ];

    // Many-to-One
    public function gedung()
    {
        return $this->belongsTo(Gedung::class, 'id_gedung');
    }
}
