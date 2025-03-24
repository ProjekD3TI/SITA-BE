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
    // protected $with = ['requestPembimbing', 'tugasAkhir',];
    protected $fillable = [
        'name',
        'email',
        'password',
        'nik',
        'status',
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

    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class, 'jabatan_dosen', 'dosen_id', 'jabatan_id')
            ->withTimestamps()
            ->withPivot('is_active');
    }

    public function tugasAkhir()
    {
        return $this->belongsToMany(TugasAkhir::class, 'dosen_penguji', 'dosen_id', 'tugas_akhir_id')
            ->using(DosenPenguji::class)
            ->withPivot(['revisi', 'catatan_semhas', 'nilai_ujian_ta'])
            ->withTimestamps();
    }

    public function requestPembimbing()
    {
        return $this->hasMany(RequestPembimbing::class);
    }

}

