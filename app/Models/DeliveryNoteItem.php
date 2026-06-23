<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = ['delivery_note_id', 'part_id', 'quantity'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
