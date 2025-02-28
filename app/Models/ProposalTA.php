<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTA extends Model
{
    use HasFactory;
    protected $table = 'proposal_ta';
    protected $fillable = [
        'mahasiswa_id',
        'judul_proposal',
        'file_proposal',
        'status_proposal',
        'tanggal_unggah'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class);
    }

    public function penilaianProposalTA()
    {
        return $this->hasMany(PenilaianProposalTA::class, 'proposal_ta_id', 'id');
    }
}
