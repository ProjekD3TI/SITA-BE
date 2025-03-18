<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;
    protected $table = 'proposal';
    protected $fillable = [
        'judul_proposal',
        'file_proposal',
        'file_transkrip',
        'status_proposal',
        'tanggal_unggah'
    ];

    public function tugasAkhir()
    {
        return $this->hasOne(TugasAkhir::class);
    }

}
