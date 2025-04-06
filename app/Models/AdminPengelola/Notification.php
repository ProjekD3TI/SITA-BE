<?php

namespace App\Models\AdminPengelola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_notification';
    
    protected $fillable = [
        'id_peminjam',
        'title',
        'message',
        'type',
        'notifiable_type',
        'notifiable_id',
        'for_role',
        'status',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function peminjam()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_peminjam');
    }
}
