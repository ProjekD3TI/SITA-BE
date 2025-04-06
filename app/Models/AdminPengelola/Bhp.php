<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bhp extends Model
{
    use HasFactory;
    protected $table = 'bhps';
    protected $primaryKey = 'id_bhp';

    protected $fillable = [
        'nama_bhp',
        'kondisi_bagus',
        'kondisi_rusak',
        'qr_code_path',
        'peruntukan', //input string ini
    ];
    public function fotos() {
        return $this->hasMany(Foto::class, 'id_bhp');
    }
}
