<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBookEntry extends Model
{
    protected $fillable = [
        'entry_no', 'branch_id', 'entry_date', 'entry_type', 'account_id',
        'currency_id', 'exchange_rate', 'amount', 'foreign_amount',
        'party_type', 'party_id', 'reference_no', 'reference_type', 'reference_id',
        'description', 'running_balance', 'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'amount' => 'decimal:2',
        'foreign_amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
