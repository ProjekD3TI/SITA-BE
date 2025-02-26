<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JabatanDosen extends Model
{
    use HasFactory;
    protected $table = 'jabatan_dosen';
    protected $primaryKey = 'id';
    protected $fillable = [
        'dosen_id',
        'jabatan_id',
        'is_active',
    ];
    public function jabatan() {
        return $this->belongsTo(Jabatan::class);
    }

    public function dosen() {
        return $this->belongsTo(Dosen::class);
    }
}
