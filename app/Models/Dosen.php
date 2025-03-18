<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Dosen extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'dosen';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'status',
        'no_telp'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function jabatans() {
        return $this->belongsToMany(Jabatan::class, 'jabatan_dosen', 'dosen_id', 'jabatan_id')
                    ->withTimestamps()
                    ->withPivot('is_active');
    }
}

