<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSession extends Model
{
    protected $fillable = [
        'session_no', 'pos_terminal_id', 'branch_id', 'user_id',
        'opened_at', 'closed_at', 'opening_float', 'closing_float',
        'total_sales', 'status',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_float' => 'decimal:2',
        'closing_float' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
