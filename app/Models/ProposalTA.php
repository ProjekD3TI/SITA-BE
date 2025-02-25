<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTA extends Model
{
    use HasFactory;
    protected $table = 'proposal_ta';
    protected $fillable = [
        'nim',
        'judul_proposal',
        'file_proposal',
        'status_proposal',
        'tanggal_unggah'
    ];
}
