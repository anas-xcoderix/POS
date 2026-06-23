<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportCashVoucher extends Model
{
    protected $fillable = [
        'voucher_no', 'branch_id', 'transport_driver_id', 'voucher_date', 'total_amount',
        'status', 'cash_book_entry_id', 'remarks', 'created_by', 'posted_by', 'posted_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'total_amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TransportDriver::class, 'transport_driver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransportCashVoucherItem::class);
    }

    public function cashBookEntry(): BelongsTo
    {
        return $this->belongsTo(CashBookEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
