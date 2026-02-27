<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    protected $guarded = [];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
