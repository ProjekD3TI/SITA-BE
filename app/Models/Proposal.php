<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;
    protected $table = 'proposal';
    // protected $with = 'tugasAkhir';
    protected $fillable = [
        'judul_proposal',
        'status_proposal',
        'tanggal_unggah'
    ];

    public function tugasAkhir()
    {
        return $this->hasOne(TugasAkhir::class);
    }

    public function berkas()
    {
        return $this->morphMany(Berkas::class, 'berkasable');
    }

}
