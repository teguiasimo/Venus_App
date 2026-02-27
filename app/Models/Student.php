<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'full_name',
        'original_photo_path',
        'processed_photo_path',
        'classroom_id',
    ];
}
