<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model is in the same namespace
use App\Models\Dosen; // Assuming Dosen model is in the AdminPengelola namespace


class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_student',
        'id_dosen',
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
    public function mahasiswa()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_student');
    }
    
    public function dosen()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_dosen');
    }
    
    public function getUserAttribute()
    {
        return $this->mahasiswa ?: $this->dosen;
    }
    public function ruang() {
        return $this->belongsTo(Ruang::class, 'id_ruang');
    }
    
    public function startSession() {
        return $this->belongsTo(Session::class, 'start_session');
    }
    
    public function endSession() {
        return $this->belongsTo(Session::class, 'end_session');
    }
}