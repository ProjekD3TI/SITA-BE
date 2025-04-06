<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto';
    protected $primaryKey = 'foto_id';

    protected $fillable = [
        'id_ruang',
        'id_jenis_inventaris',
        'id_bhp',
        'foto_path',
    ];
    
    public function bhp() {
        return $this->belongsTo(Bhp::class, 'id_bhp');
    }
}
