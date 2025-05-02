<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semester';
    protected $with = ['tugasAkhir', 'ujianTA'];
    protected $fillable = ['nama_semester', 'is_default'];

    public function tugasAkhir()
    {
        return $this->hasMany(TugasAkhir::class);
    }

    public function ujianTA()
    {
        return $this->hasMany(UjianTA::class);
    }
}
