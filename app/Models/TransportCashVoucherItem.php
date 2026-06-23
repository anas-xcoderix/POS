<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportCashVoucherItem extends Model
{
    protected $fillable = [
        'transport_cash_voucher_id', 'transport_shipment_id', 'amount',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(TransportCashVoucher::class, 'transport_cash_voucher_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(TransportShipment::class, 'transport_shipment_id');
    }
}
