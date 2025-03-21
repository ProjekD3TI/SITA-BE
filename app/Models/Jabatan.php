<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;
    protected $table = 'jabatan';
    protected $primaryKey = 'id';
    protected $with = 'dosen';
    protected $fillable = [
        'name',
        'description',
    ];
    public function dosen()
    {
        return $this->belongsToMany(Dosen::class, 'jabatan_dosen', 'jabatan_id', 'dosen_id');
    }
}
