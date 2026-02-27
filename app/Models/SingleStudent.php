<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SingleStudent extends Model
{
    protected $fillable = [
        'full_name',
        'classroom_name',
        'code',
        'original_photo_path',
        'processed_photo_path',
    ];
}
