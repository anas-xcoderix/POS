<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickTicketItem extends Model
{
    protected $fillable = [
        'pick_ticket_id', 'sales_invoice_item_id', 'part_id', 'location_id',
        'stock_batch_id', 'qty_ordered', 'qty_picked',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:2',
        'qty_picked' => 'decimal:2',
    ];

    public function pickTicket(): BelongsTo
    {
        return $this->belongsTo(PickTicket::class);
    }

    public function salesInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(SalesInvoiceItem::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }
}
