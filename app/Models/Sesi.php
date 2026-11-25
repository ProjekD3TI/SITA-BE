<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'sesi';
    protected $with = 'ujianTA';
    protected $fillable = ['nama_sesi', 'waktu_mulai', 'waktu_selesai'];

    public function ujianTA()
    {
        return $this->hasMany(UjianTA::class);
    }

}
