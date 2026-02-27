<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(CorrectionBatch::class, 'correction_batch_id');
    }
}
