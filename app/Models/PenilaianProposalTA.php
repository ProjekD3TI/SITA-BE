<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianProposalTA extends Model
{
    use HasFactory;
    protected $table = 'penilaian_proposal_ta';

    protected $fillable = [
        'dosen_id',
        'proposal_ta_id',
        'nilai',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function proposalTA()
    {
        return $this->belongsTo(ProposalTA::class);
    }
}
