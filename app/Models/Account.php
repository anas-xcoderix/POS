<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'account_code', 'name', 'name_ar', 'account_type', 'parent_id',
        'opening_balance', 'current_balance', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Account $account) {
            if (! $account->current_balance && $account->opening_balance) {
                $account->current_balance = $account->opening_balance;
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function isDebitNormal(): bool
    {
        return in_array($this->account_type, ['asset', 'expense'], true);
    }
}
