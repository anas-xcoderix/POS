<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PurchaseInvoice;
use App\Models\SaleReturn;
use App\Models\SalesInvoice;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function getBalance(int $branchId, int $locationId, int $partId): float
    {
        return (float) StockBalance::query()
            ->where('branch_id', $branchId)
            ->where('location_id', $locationId)
            ->where('part_id', $partId)
            ->value('quantity') ?? 0;
    }

    public function adjustStock(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantityChange,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $referenceNo = null,
        float $unitCost = 0,
        ?int $userId = null,
        ?string $remarks = null
    ): StockBalance {
        return DB::transaction(function () use (
            $branchId, $locationId, $partId, $quantityChange, $movementType,
            $referenceType, $referenceId, $referenceNo, $unitCost, $userId, $remarks
        ) {
            $balance = StockBalance::query()->firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'location_id' => $locationId,
                    'part_id' => $partId,
                ],
                ['quantity' => 0, 'reserved_qty' => 0, 'avg_cost' => 0]
            );

            $newQty = (float) $balance->quantity + $quantityChange;

            if ($newQty < 0) {
                $part = Part::find($partId);
                throw new \RuntimeException('Insufficient stock for part: '.($part?->part_number ?? $partId));
            }

            if ($quantityChange > 0 && $unitCost > 0) {
                $totalValue = ($balance->quantity * $balance->avg_cost) + ($quantityChange * $unitCost);
                $balance->avg_cost = $newQty > 0 ? $totalValue / $newQty : $unitCost;
            } elseif ($quantityChange > 0 && $balance->avg_cost > 0) {
                // keep avg cost on transfer in / return
            }

            $balance->quantity = $newQty;
            $balance->save();

            StockMovement::create([
                'branch_id' => $branchId,
                'location_id' => $locationId,
                'part_id' => $partId,
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'quantity_in' => $quantityChange > 0 ? $quantityChange : 0,
                'quantity_out' => $quantityChange < 0 ? abs($quantityChange) : 0,
                'unit_cost' => $unitCost ?: $balance->avg_cost,
                'balance_after' => $newQty,
                'user_id' => $userId,
                'remarks' => $remarks,
                'movement_date' => now(),
            ]);

            return $balance->fresh();
        });
    }

    public function manualAdjustment(
        int $branchId,
        int $locationId,
        int $partId,
        float $newQuantity,
        ?int $userId = null,
        ?string $remarks = null
    ): StockBalance {
        $current = $this->getBalance($branchId, $locationId, $partId);
        $change = $newQuantity - $current;

        if ($change == 0) {
            return StockBalance::query()->where([
                'branch_id' => $branchId,
                'location_id' => $locationId,
                'part_id' => $partId,
            ])->firstOrFail();
        }

        return $this->adjustStock(
            $branchId, $locationId, $partId, $change,
            'adjustment', null, null, 'ADJ-'.now()->format('YmdHis'),
            0, $userId, $remarks ?? 'Manual stock adjustment'
        );
    }

    public function receivePurchase(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantity,
        float $unitCost,
        int $purchaseInvoiceId,
        string $invoiceNo,
        ?int $userId = null
    ): StockBalance {
        $balance = $this->adjustStock(
            $branchId, $locationId, $partId, $quantity,
            'purchase_receive', PurchaseInvoice::class, $purchaseInvoiceId, $invoiceNo,
            $unitCost, $userId
        );

        Part::where('id', $partId)->update(['cost_price' => $balance->avg_cost]);

        return $balance;
    }

    public function issueForSale(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantity,
        int $salesInvoiceId,
        string $invoiceNo,
        ?int $userId = null
    ): StockBalance {
        $balance = StockBalance::query()
            ->where('branch_id', $branchId)
            ->where('location_id', $locationId)
            ->where('part_id', $partId)
            ->first();

        $unitCost = $balance?->avg_cost ?? Part::find($partId)?->cost_price ?? 0;

        return $this->adjustStock(
            $branchId, $locationId, $partId, -$quantity,
            'sale_issue', SalesInvoice::class, $salesInvoiceId, $invoiceNo,
            $unitCost, $userId
        );
    }

    public function receiveSaleReturn(
        int $branchId,
        int $locationId,
        int $partId,
        float $quantity,
        float $unitCost,
        int $saleReturnId,
        string $returnNo,
        ?int $userId = null
    ): StockBalance {
        return $this->adjustStock(
            $branchId, $locationId, $partId, $quantity,
            'sale_return', SaleReturn::class, $saleReturnId, $returnNo,
            $unitCost, $userId
        );
    }

    public function completeTransfer(StockTransfer $transfer, ?int $userId = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            if ($transfer->status === 'completed') {
                return $transfer;
            }

            $transfer->load('items.part');

            foreach ($transfer->items as $item) {
                $balance = StockBalance::query()
                    ->where('branch_id', $transfer->from_branch_id)
                    ->where('location_id', $item->from_location_id)
                    ->where('part_id', $item->part_id)
                    ->first();

                $unitCost = $balance?->avg_cost ?? $item->unit_cost ?? $item->part?->cost_price ?? 0;

                $this->adjustStock(
                    $transfer->from_branch_id,
                    $item->from_location_id,
                    $item->part_id,
                    -(float) $item->quantity,
                    'transfer_out',
                    StockTransfer::class,
                    $transfer->id,
                    $transfer->transfer_no,
                    $unitCost,
                    $userId
                );

                $this->adjustStock(
                    $transfer->to_branch_id,
                    $item->to_location_id,
                    $item->part_id,
                    (float) $item->quantity,
                    'transfer_in',
                    StockTransfer::class,
                    $transfer->id,
                    $transfer->transfer_no,
                    $unitCost,
                    $userId
                );
            }

            $transfer->update(['status' => 'completed']);

            return $transfer->fresh(['items.part', 'fromBranch', 'toBranch']);
        });
    }
}
