<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merk extends Model
{
    use HasFactory;
    protected $table = 'merk';
    protected $primaryKey = 'id_merk';

    protected $fillable = [
        'nama_merk',
    ];
    public function jenisInventaris()
    {
        return $this->hasMany(JenisInventaris::class, 'id_tipe');
    }
}
