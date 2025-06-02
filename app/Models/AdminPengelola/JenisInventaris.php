<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisInventaris extends Model
{
    use HasFactory;
    protected $table = 'jenis_inventaris';
    protected $primaryKey = 'id_jenis_inventaris';

    protected $fillable = [
        'nama_inventaris',
        'id_merk',
        'id_tipe',
        'peruntukan',
        'status_import',
    ];
    public function fotos() {
        return $this->hasMany(Foto::class, 'id_jenis_inventaris');
    }
    public function merk()
    {
        return $this->belongsTo(Merk::class, 'id_merk');
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'id_tipe');
    }

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class, 'id_jenis_inventaris');
    }

}
