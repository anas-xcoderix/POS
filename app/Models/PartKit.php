<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartKit extends Model
{
    protected $fillable = ['kit_part_id', 'component_part_id', 'quantity'];

    public function kitPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'kit_part_id');
    }

    public function componentPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'component_part_id');
    }
}
