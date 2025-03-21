<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';
    protected $with = ['tugasAkhir', 'ujianTA'];
    protected $fillable = ['tahun_ajaran'];

    public function tugasAkhir()
    {
        return $this->hasMany(TugasAkhir::class);
    }

    public function ujianTA()
    {
        return $this->hasMany(UjianTA::class);
    }
}

