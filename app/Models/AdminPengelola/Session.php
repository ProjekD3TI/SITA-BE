<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name_session',
        'start_time',
        'end_time',
    ];
    public function fixedScheduleSessions()
    {
        return $this->hasMany(FixedScheduleSession::class, 'id_session');
    }
}
