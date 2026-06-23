<?php

namespace App\Services;

use App\Models\JobCard;
use App\Models\JobCardItem;
use App\Models\Part;
use App\Models\SalesInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkshopService
{
    public function __construct(
        private BranchScopeService $branchScope,
        private SalesService $salesService,
        private TaxService $taxService,
    ) {}

    public function createJobCard(array $data, array $items): JobCard
    {
        return DB::transaction(function () use ($data, $items) {
            $this->branchScope->assertBranchAccess((int) $data['branch_id']);

            $jobCard = JobCard::create($data);
            $this->syncItems($jobCard, $items);
            $this->recalculate($jobCard);

            return $jobCard->fresh(['items.part', 'customer', 'vehicle', 'branch', 'mechanic']);
        });
    }

    public function updateStatus(JobCard $jobCard, string $status): JobCard
    {
        $allowed = ['open', 'in_progress', 'completed', 'cancelled'];
        if (! in_array($status, $allowed, true)) {
            throw new \RuntimeException('Invalid job card status.');
        }

        if ($jobCard->status === 'invoiced') {
            throw new \RuntimeException('Cannot change status of an invoiced job card.');
        }

        $jobCard->update(['status' => $status]);

        return $jobCard->fresh();
    }

    public function complete(JobCard $jobCard): JobCard
    {
        if ($jobCard->status === 'invoiced') {
            throw new \RuntimeException('Job card already invoiced.');
        }

        if ($jobCard->status === 'cancelled') {
            throw new \RuntimeException('Cannot complete a cancelled job card.');
        }

        $jobCard->update(['status' => 'completed']);

        return $jobCard->fresh();
    }

    public function convertToInvoice(JobCard $jobCard, array $invoiceData, ?int $userId = null): SalesInvoice
    {
        return DB::transaction(function () use ($jobCard, $invoiceData, $userId) {
            $jobCard->load(['items.part', 'customer', 'branch']);

            if ($jobCard->sales_invoice_id) {
                throw new \RuntimeException('Job card already converted to invoice.');
            }

            if (! in_array($jobCard->status, ['completed', 'in_progress', 'open'], true)) {
                throw new \RuntimeException('Job card cannot be invoiced in current status.');
            }

            $laborPart = Part::where('part_number', 'SVC-LABOR')->first();
            if (! $laborPart) {
                throw new \RuntimeException('Workshop labor part SVC-LABOR not found. Run database seeder.');
            }

            $invoiceItems = [];
            $locationId = $invoiceData['default_location_id'] ?? $jobCard->location_id;

            foreach ($jobCard->items as $item) {
                if ($item->item_type === 'part' && $item->part_id) {
                    $invoiceItems[] = [
                        'part_id' => $item->part_id,
                        'location_id' => $locationId,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percent' => 0,
                        'vat_percent' => $this->taxService->resolveVatPercent(),
                    ];
                } elseif ($item->item_type === 'labor' && (float) $item->line_total > 0) {
                    $invoiceItems[] = [
                        'part_id' => $laborPart->id,
                        'location_id' => $locationId,
                        'quantity' => 1,
                        'unit_price' => $item->line_total,
                        'discount_percent' => 0,
                        'vat_percent' => $this->taxService->resolveVatPercent(),
                    ];
                }
            }

            if ($invoiceItems === []) {
                throw new \RuntimeException('No billable items on this job card.');
            }

            $postStock = ($invoiceData['status'] ?? 'draft') === 'posted';
            $data = array_merge([
                'branch_id' => $jobCard->branch_id,
                'customer_id' => $jobCard->customer_id,
                'invoice_date' => $invoiceData['invoice_date'] ?? now()->toDateString(),
                'invoice_type' => $invoiceData['invoice_type'] ?? 'cash',
                'status' => $invoiceData['status'] ?? 'draft',
                'remarks' => trim('Job card '.$jobCard->job_no.($jobCard->complaint ? ' — '.$jobCard->complaint : '')),
                'created_by' => $userId,
            ], $invoiceData);

            $invoice = $this->salesService->createInvoice($data, $invoiceItems, $postStock);

            $jobCard->update([
                'status' => 'invoiced',
                'sales_invoice_id' => $invoice->id,
            ]);

            return $invoice->fresh(['items.part', 'customer', 'branch']);
        });
    }

    public function wipReport(?int $branchId = null): Collection
    {
        $query = JobCard::query()
            ->with(['customer', 'vehicle', 'mechanic', 'branch'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('promised_date')
            ->orderBy('job_date');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    protected function syncItems(JobCard $jobCard, array $items): void
    {
        $jobCard->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $type = $item['item_type'] ?? 'part';

            JobCardItem::create([
                'job_card_id' => $jobCard->id,
                'item_type' => $type,
                'part_id' => $type === 'part' ? ($item['part_id'] ?? null) : null,
                'description' => $item['description'] ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'line_total' => round($qty * $price, 2),
            ]);
        }
    }

    protected function recalculate(JobCard $jobCard): void
    {
        $jobCard->load('items');

        $partsTotal = round($jobCard->items->where('item_type', 'part')->sum('line_total'), 2);
        $laborTotal = round($jobCard->items->where('item_type', 'labor')->sum('line_total'), 2);

        $jobCard->update([
            'parts_total' => $partsTotal,
            'labor_total' => $laborTotal,
            'total_amount' => round($partsTotal + $laborTotal, 2),
        ]);
    }

    public function nextJobNo(): string
    {
        $prefix = 'JC-'.now()->format('Ymd').'-';
        $last = JobCard::where('job_no', 'like', $prefix.'%')->orderByDesc('job_no')->value('job_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
