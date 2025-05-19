<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;

    protected $fillable = [
        'berkasable_type',
        'berkasable_id',
        'nama_berkas',
        'file_path',
        'status_valid',
    ];

    public function parent()
    {
        return $this->morphTo();
    }
}
