<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedSchedule extends Model
{
    use HasFactory;
    protected $table = 'fixed_schedules';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_class',
        'id_ruang',
        'pemakaian',
        'id_school_year',
        'start_date',
        'end_date',
        'penanggung_jawab',
    ];

    public function sessions()
    {
        return $this->hasMany(FixedScheduleSession::class, 'id_fixed_schedule');
    }
    
    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'id_ruang');
    }
    public function dosen()
{
    return $this->belongsTo(Dosen::class, 'penanggung_jawab');
}
public function schoolYear()
{
    return $this->belongsTo(SchoolYear::class, 'id_school_year');
}
}