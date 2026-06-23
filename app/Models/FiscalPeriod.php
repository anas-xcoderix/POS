<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalPeriod extends Model
{
    protected $fillable = ['year', 'month', 'is_closed', 'closed_at', 'closed_by'];

    protected $casts = [
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function label(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
