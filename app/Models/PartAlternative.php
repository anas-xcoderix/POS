<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartAlternative extends Model
{
    protected $fillable = ['part_id', 'alternative_part_id', 'notes'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function alternativePart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'alternative_part_id');
    }
}
