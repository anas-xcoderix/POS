<?php

namespace App\Services;

use App\Models\StockCountItem;
use App\Models\StockCountSession;
use Illuminate\Support\Facades\DB;

class StockCountService
{
    public function __construct(
        private StockService $stockService,
        private FiscalPeriodService $fiscalPeriod,
    ) {}

    public function createSession(array $data, array $items): StockCountSession
    {
        return DB::transaction(function () use ($data, $items) {
            $session = StockCountSession::create($data);

            foreach ($items as $row) {
                $systemQty = $this->stockService->getBalance(
                    (int) $data['branch_id'],
                    (int) $row['location_id'],
                    (int) $row['part_id']
                );
                $counted = (float) $row['counted_qty'];

                StockCountItem::create([
                    'stock_count_session_id' => $session->id,
                    'part_id' => $row['part_id'],
                    'location_id' => $row['location_id'],
                    'system_qty' => $systemQty,
                    'counted_qty' => $counted,
                    'variance' => $counted - $systemQty,
                ]);
            }

            return $session->fresh(['items.part', 'items.location', 'branch']);
        });
    }

    public function postSession(StockCountSession $session, ?int $userId = null): StockCountSession
    {
        return DB::transaction(function () use ($session, $userId) {
            if ($session->status === 'posted') {
                return $session;
            }

            $this->fiscalPeriod->assertOpen($session->count_date);
            $session->load('items.part');

            foreach ($session->items as $item) {
                if ((float) $item->variance === 0.0) {
                    continue;
                }

                $this->stockService->manualAdjustment(
                    $session->branch_id,
                    $item->location_id,
                    $item->part_id,
                    (float) $item->counted_qty,
                    $userId,
                    'Physical count '.$session->count_no
                );
            }

            $session->update(['status' => 'posted', 'posted_at' => now()]);

            return $session->fresh(['items.part', 'branch']);
        });
    }
}
