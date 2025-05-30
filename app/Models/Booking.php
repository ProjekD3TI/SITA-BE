<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_student',
        'id_ruang',
        'date',
        'start_session',
        'end_session',
        'keterangan',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'files',
    ];
}

