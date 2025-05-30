<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedScheduleSession extends Model
{
    use HasFactory;
    protected $table = 'fixed_schedule_sessions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_fixed_schedule',
        'id_session',
        'day_of_week', //int 1 = senin s/d 7 = minggu
    ];

    public function fixedSchedule()
    {
        return $this->belongsTo(FixedSchedule::class, 'id_fixed_schedule');
    }
    
    public function session()
    {
        return $this->belongsTo(Session::class, 'id_session');
    }
}
