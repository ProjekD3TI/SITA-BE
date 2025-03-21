<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;
    protected $table = 'ruang';
    protected $with = 'ujianTA';
    protected $fillable = ['nama_ruang'];


    public function ujianTA()
    {
        return $this->hasMany(UjianTA::class);
    }
}
