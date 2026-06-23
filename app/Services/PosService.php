<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Models\SalesInvoice;
use App\Models\Location;
use Illuminate\Support\Collection;

class PosService
{
    public function __construct(
        private SalesService $salesService,
        private StockService $stockService,
        private AuditService $audit,
        private BranchScopeService $branchScope,
    ) {}

    public function openSession(PosTerminal $terminal, float $openingFloat, int $userId): PosSession
    {
        $existing = PosSession::where('pos_terminal_id', $terminal->id)->where('status', 'open')->first();
        if ($existing) {
            throw new \RuntimeException(__('messages.pos.session_already_open'));
        }

        $session = PosSession::create([
            'session_no' => $this->nextSessionNo(),
            'pos_terminal_id' => $terminal->id,
            'branch_id' => $terminal->branch_id,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_float' => $openingFloat,
            'status' => 'open',
        ]);

        $this->audit->log('pos.session_opened', $session, null, $session->toArray(), $session->session_no);

        return $session;
    }

    public function closeSession(PosSession $session, float $closingFloat): PosSession
    {
        if ($session->status !== 'open') {
            throw new \RuntimeException(__('messages.pos.session_not_open'));
        }

        $session->update([
            'closing_float' => $closingFloat,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        $this->audit->log('pos.session_closed', $session, null, [
            'closing_float' => $closingFloat,
            'stats' => $this->sessionStats($session),
        ], $session->session_no);

        return $session->fresh();
    }

    public function quickSale(PosSession $session, array $data, array $items, bool $post = true): SalesInvoice
    {
        $this->branchScope->assertBranchAccess($session->branch_id);

        $terminal = $session->posTerminal;
        $defaultLocation = $this->defaultLocation($terminal, $session->branch_id);

        foreach ($items as &$item) {
            $item['location_id'] = $item['location_id'] ?? $defaultLocation;
            $item['manual_price'] = true;

            $available = $this->stockService->getAvailable(
                $session->branch_id,
                (int) $item['location_id'],
                (int) $item['part_id']
            );

            if ($post && (float) $item['quantity'] > $available) {
                $part = Part::find($item['part_id']);
                throw new \RuntimeException(__('pos.insufficient_stock', [
                    'part' => $part?->part_number ?? $item['part_id'],
                    'qty' => number_format($available, 2),
                ]));
            }
        }

        $invoiceType = $data['invoice_type'] ?? 'cash';
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $payload = array_merge($data, [
            'branch_id' => $session->branch_id,
            'pos_session_id' => $session->id,
            'source' => 'pos',
            'invoice_type' => $invoiceType,
            'status' => $post ? 'posted' : 'draft',
            'created_by' => $session->user_id,
            'paid_amount' => $invoiceType === 'cash' ? $paidAmount : 0,
        ]);

        $invoice = $this->salesService->createInvoice($payload, $items, $post);
        $session->increment('total_sales', (float) $invoice->total_amount);
        $this->audit->log('pos.sale', $invoice, null, ['total' => $invoice->total_amount], $invoice->invoice_no);

        return $invoice;
    }

    public function searchParts(int $branchId, ?int $locationId, string $query, int $limit = 20): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        return Part::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('part_number', 'like', "%{$query}%")
                    ->orWhere('barcode', $query)
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhere('oem_no', 'like', "%{$query}%")
                    ->orWhere('description_en', 'like', "%{$query}%")
                    ->orWhere('description_ar', 'like', "%{$query}%");
            })
            ->orderBy('part_number')
            ->limit($limit)
            ->get()
            ->map(function (Part $part) use ($branchId, $locationId) {
                $stock = $locationId
                    ? $this->stockService->getAvailable($branchId, $locationId, $part->id)
                    : null;

                return [
                    'id' => $part->id,
                    'part_number' => $part->part_number,
                    'barcode' => $part->barcode,
                    'description' => localized($part, 'description_en', 'description_ar'),
                    'list_price' => (float) $part->list_price,
                    'stock' => $stock,
                ];
            });
    }

    public function sessionStats(PosSession $session): array
    {
        $invoices = SalesInvoice::with('customer')
            ->where('pos_session_id', $session->id)
            ->where('status', 'posted')
            ->whereNull('voided_at')
            ->orderByDesc('id')
            ->get();

        $cashSales = (float) $invoices->where('invoice_type', 'cash')->sum('total_amount');
        $creditSales = (float) $invoices->where('invoice_type', 'credit')->sum('total_amount');

        return [
            'invoice_count' => $invoices->count(),
            'total_sales' => (float) $session->total_sales,
            'cash_sales' => round($cashSales, 2),
            'credit_sales' => round($creditSales, 2),
            'expected_cash' => round((float) $session->opening_float + $cashSales, 2),
            'recent' => $invoices->take(15)->values(),
        ];
    }

    public function nextInvoiceNo(): string
    {
        $seq = SalesInvoice::where('source', 'pos')
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'POS-'.now()->format('Ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function nextSessionNo(): string
    {
        return 'POS-'.now()->format('YmdHis');
    }

    protected function defaultLocation(PosTerminal $terminal, int $branchId): ?int
    {
        return $terminal->default_location_id
            ?? Location::where('branch_id', $branchId)->where('location_type', 'showroom')->value('id')
            ?? Location::where('branch_id', $branchId)->value('id');
    }
}
