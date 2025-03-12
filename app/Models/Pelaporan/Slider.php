<?php

namespace App\Models\Pelaporan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $table = 'slider';
    protected $primaryKey = 'slider_id';

    protected $fillable = [
        'image_path', 
        'text',
    ];
}
