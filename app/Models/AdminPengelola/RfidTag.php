<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Dosen;

class RfidTag extends Model
{
    use HasFactory;
    protected $table = 'rfid_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_student',
        'id_dosen',
        'rfid_code',
    ];
    public function mahasiswa()
    {
        return $this->belongsTo(\App\Models\User::class, 'mahasiswa_id');
    }
    
    public function dosen()
    {
        return $this->belongsTo(\App\Models\Dosen::class, 'dosen_id');
    }
}