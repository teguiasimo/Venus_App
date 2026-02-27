<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionBatch extends Model
{
    protected $guarded = [];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function requests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
