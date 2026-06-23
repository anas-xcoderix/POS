<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\Part;
use App\Models\PayrollRun;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\SaleReturn;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        private AccountingService $accounting,
        private WorkshopService $workshop,
        private HrService $hr,
        private SettingService $settings,
    ) {}

    public function definition(string $key): array
    {
        $def = config("reports.reports.{$key}");
        if (! $def) {
            throw new \InvalidArgumentException("Unknown report: {$key}");
        }

        return $def;
    }

    public function allDefinitions(): array
    {
        return config('reports.reports', []);
    }

    public function categories(): array
    {
        return config('reports.categories', []);
    }

    public function generate(string $key, array $filters = []): array
    {
        $def = $this->definition($key);
        $locale = $filters['locale'] ?? 'en';
        $filters = $this->normalizeFilters($filters, $def);

        $method = 'report'.str_replace(' ', '', ucwords(str_replace('-', ' ', $key)));

        if (! method_exists($this, $method)) {
            throw new \RuntimeException("Report handler missing: {$method}");
        }

        $payload = $this->{$method}($filters);
        $payload['meta'] = array_merge([
            'key' => $key,
            'title' => $locale === 'ar' ? ($def['title_ar'] ?? $def['title']) : $def['title'],
            'title_en' => $def['title'],
            'title_ar' => $def['title_ar'] ?? $def['title'],
            'legacy' => $def['legacy'] ?? null,
            'category' => $def['category'],
            'view' => $def['view'] ?? 'table',
            'locale' => $locale,
            'company' => $this->settings->get('company_name', config('app.name')),
            'generated_at' => now()->format('Y-m-d H:i'),
            'filters' => $filters,
        ], $payload['meta'] ?? []);

        return $payload;
    }

    protected function normalizeFilters(array $filters, array $def): array
    {
        $allowed = $def['filters'] ?? [];
        $normalized = [
            'locale' => in_array($filters['locale'] ?? 'en', ['en', 'ar'], true) ? $filters['locale'] : 'en',
            'from' => $filters['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $filters['to'] ?? now()->toDateString(),
            'as_of' => $filters['as_of'] ?? now()->toDateString(),
            'branch_id' => $filters['branch_id'] ?? null,
            'part_id' => $filters['part_id'] ?? null,
            'search' => $filters['search'] ?? null,
            'movement_type' => $filters['movement_type'] ?? null,
            'status' => $filters['status'] ?? null,
            'days' => (int) ($filters['days'] ?? 90),
        ];

        return array_intersect_key($normalized, array_flip(array_merge(['locale'], $allowed)));
    }

    protected function table(array $columns, Collection|array $rows, array $summary = []): array
    {
        return [
            'columns' => $columns,
            'rows' => collect($rows)->values()->all(),
            'summary' => $summary,
        ];
    }

    protected function col(string $key, string $label, string $labelAr, string $align = 'left'): array
    {
        return compact('key', 'label', 'labelAr', 'align');
    }

    protected function money($value): string
    {
        return number_format((float) $value, 2);
    }

    // --- Master reports ---

    protected function reportCustomerList(array $f): array
    {
        $rows = Customer::with('branch')->orderBy('code')->get()->map(fn ($c) => [
            'code' => $c->code,
            'name' => $f['locale'] === 'ar' && $c->name_ar ? $c->name_ar : $c->name,
            'type' => $c->customer_type,
            'branch' => $c->branch?->name,
            'phone' => $c->phone,
            'credit_limit' => $this->money($c->credit_limit),
            'balance' => $this->money($c->balance),
            'active' => $c->is_active ? 'Yes' : 'No',
        ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Name', 'الاسم'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('phone', 'Phone', 'الهاتف'),
            $this->col('credit_limit', 'Credit Limit', 'حد الائتمان', 'right'),
            $this->col('balance', 'Balance', 'الرصيد', 'right'),
            $this->col('active', 'Active', 'نشط'),
        ], $rows, ['total' => $rows->count().' customers']);
    }

    protected function reportVendorList(array $f): array
    {
        $rows = Vendor::orderBy('code')->get()->map(fn ($v) => [
            'code' => $v->code,
            'name' => $v->name,
            'phone' => $v->phone,
            'email' => $v->email,
            'balance' => $this->money($v->balance ?? 0),
            'active' => $v->is_active ? 'Yes' : 'No',
        ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Name', 'الاسم'),
            $this->col('phone', 'Phone', 'الهاتف'),
            $this->col('email', 'Email', 'البريد'),
            $this->col('balance', 'Balance', 'الرصيد', 'right'),
            $this->col('active', 'Active', 'نشط'),
        ], $rows);
    }

    protected function reportPartsMaster(array $f): array
    {
        $q = Part::with(['brand', 'origin'])->where('is_active', true);
        if ($f['search'] ?? null) {
            $q->where(fn ($w) => $w->where('part_number', 'like', "%{$f['search']}%")
                ->orWhere('description_en', 'like', "%{$f['search']}%")
                ->orWhere('oem_no', 'like', "%{$f['search']}%"));
        }

        $rows = $q->orderBy('part_number')->get()->map(fn ($p) => [
            'part_number' => $p->part_number,
            'description' => $f['locale'] === 'ar' && $p->description_ar ? $p->description_ar : $p->description_en,
            'brand' => $p->brand?->name,
            'oem' => $p->oem_no,
            'list_price' => $this->money($p->list_price),
            'cost' => $this->money($p->cost_price),
            'min_stock' => $p->min_stock,
        ]);

        return $this->table([
            $this->col('part_number', 'Part No', 'رقم القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('brand', 'Brand', 'العلامة'),
            $this->col('oem', 'OEM', 'رقم OEM'),
            $this->col('list_price', 'List Price', 'سعر القائمة', 'right'),
            $this->col('cost', 'Cost', 'التكلفة', 'right'),
            $this->col('min_stock', 'Min Stock', 'الحد الأدنى', 'right'),
        ], $rows);
    }

    // --- Sales reports ---

    protected function salesInvoiceQuery(array $f)
    {
        return SalesInvoice::with(['customer', 'branch'])
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b));
    }

    protected function reportSaleSummary(array $f): array
    {
        $invoices = $this->salesInvoiceQuery($f)->where('status', 'posted')->get();
        $rows = $invoices->map(fn ($i) => [
            'invoice_no' => $i->invoice_no,
            'date' => $i->invoice_date?->format('Y-m-d'),
            'customer' => $i->customer?->name,
            'branch' => $i->branch?->name,
            'type' => $i->invoice_type,
            'subtotal' => $this->money($i->subtotal),
            'vat' => $this->money($i->vat_amount),
            'total' => $this->money($i->total_amount),
        ]);

        return $this->table([
            $this->col('invoice_no', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('subtotal', 'Subtotal', 'المجموع', 'right'),
            $this->col('vat', 'VAT', 'الضريبة', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows, [
            'count' => $invoices->count(),
            'total' => $this->money($invoices->sum('total_amount')),
        ]);
    }

    protected function reportSalesByCustomer(array $f): array
    {
        $rows = $this->salesInvoiceQuery($f)->where('status', 'posted')
            ->select('customer_id', DB::raw('COUNT(*) as invoice_count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->map(fn ($r) => [
                'customer' => $r->customer?->name ?? '—',
                'invoices' => $r->invoice_count,
                'total' => $this->money($r->total),
            ])
            ->sortByDesc(fn ($r) => (float) str_replace(',', '', $r['total']))
            ->values();

        return $this->table([
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('invoices', 'Invoices', 'الفواتير', 'right'),
            $this->col('total', 'Total Sales', 'إجمالي المبيعات', 'right'),
        ], $rows);
    }

    protected function reportMonthlySalesBranch(array $f): array
    {
        $rows = SalesInvoice::where('status', 'posted')
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->select(
                'branch_id',
                DB::raw('YEAR(invoice_date) as yr'),
                DB::raw('MONTH(invoice_date) as mo'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('branch_id', 'yr', 'mo')
            ->with('branch')
            ->orderBy('yr')->orderBy('mo')
            ->get()
            ->map(fn ($r) => [
                'period' => sprintf('%04d-%02d', $r->yr, $r->mo),
                'branch' => $r->branch?->name,
                'invoices' => $r->invoice_count,
                'total' => $this->money($r->total),
            ]);

        return $this->table([
            $this->col('period', 'Period', 'الفترة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('invoices', 'Invoices', 'الفواتير', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportDailyTransactions(array $f): array
    {
        $sales = $this->salesInvoiceQuery($f)->get()->map(fn ($i) => [
            'date' => $i->invoice_date?->format('Y-m-d'),
            'type' => 'Sale',
            'doc_no' => $i->invoice_no,
            'party' => $i->customer?->name,
            'branch' => $i->branch?->name,
            'status' => $i->status,
            'amount' => $this->money($i->total_amount),
        ]);

        $purchases = PurchaseInvoice::with(['vendor', 'branch'])
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($i) => [
                'date' => $i->invoice_date?->format('Y-m-d'),
                'type' => 'Purchase',
                'doc_no' => $i->invoice_no,
                'party' => $i->vendor?->name,
                'branch' => $i->branch?->name,
                'status' => $i->status,
                'amount' => $this->money($i->total_amount),
            ]);

        $rows = $sales->concat($purchases)->sortBy('date')->values();

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('doc_no', 'Document', 'المستند'),
            $this->col('party', 'Party', 'الطرف'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
        ], $rows);
    }

    protected function reportSalesReturns(array $f): array
    {
        $rows = SaleReturn::with(['customer', 'branch'])
            ->whereBetween('return_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($r) => [
                'return_no' => $r->return_no,
                'date' => $r->return_date?->format('Y-m-d'),
                'customer' => $r->customer?->name,
                'branch' => $r->branch?->name,
                'status' => $r->status,
                'total' => $this->money($r->total_amount),
            ]);

        return $this->table([
            $this->col('return_no', 'Return No', 'رقم المرتجع'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportQuotations(array $f): array
    {
        $rows = Quotation::with(['customer', 'branch'])
            ->whereBetween('quotation_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($q) => [
                'quotation_no' => $q->quotation_no,
                'date' => $q->quotation_date?->format('Y-m-d'),
                'customer' => $q->customer?->name,
                'branch' => $q->branch?->name,
                'status' => $q->status,
                'total' => $this->money($q->total_amount),
            ]);

        return $this->table([
            $this->col('quotation_no', 'Quotation', 'عرض السعر'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportTopSellingParts(array $f): array
    {
        $rows = SalesInvoiceItem::query()
            ->join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('parts', 'sales_invoice_items.part_id', '=', 'parts.id')
            ->where('sales_invoices.status', 'posted')
            ->whereBetween('sales_invoices.invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('sales_invoices.branch_id', $b))
            ->select(
                'parts.part_number',
                'parts.description_en',
                DB::raw('SUM(sales_invoice_items.quantity) as qty'),
                DB::raw('SUM(sales_invoice_items.line_total) as revenue')
            )
            ->groupBy('parts.id', 'parts.part_number', 'parts.description_en')
            ->orderByDesc('qty')
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'part_number' => $r->part_number,
                'description' => $r->description_en,
                'quantity' => number_format((float) $r->qty, 2),
                'revenue' => $this->money($r->revenue),
            ]);

        return $this->table([
            $this->col('part_number', 'Part No', 'رقم القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('quantity', 'Qty Sold', 'الكمية', 'right'),
            $this->col('revenue', 'Revenue', 'الإيراد', 'right'),
        ], $rows);
    }

    // --- Inventory reports ---

    protected function stockBalanceQuery(array $f)
    {
        return StockBalance::with(['part.brand', 'branch', 'location'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['search'] ?? null, fn ($q, $s) => $q->whereHas('part', fn ($p) => $p->where('part_number', 'like', "%{$s}%")));
    }

    protected function reportStockListing(array $f): array
    {
        $rows = $this->stockBalanceQuery($f)->where('quantity', '>', 0)->get()->map(fn ($s) => [
            'part' => $s->part?->part_number,
            'description' => $s->part?->description_en,
            'branch' => $s->branch?->name,
            'location' => $s->location?->code,
            'quantity' => number_format((float) $s->quantity, 2),
            'avg_cost' => $this->money($s->avg_cost),
            'value' => $this->money((float) $s->quantity * (float) $s->avg_cost),
        ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('quantity', 'Qty', 'الكمية', 'right'),
            $this->col('avg_cost', 'Avg Cost', 'متوسط التكلفة', 'right'),
            $this->col('value', 'Value', 'القيمة', 'right'),
        ], $rows, ['total_value' => $this->money($rows->sum(fn ($r) => (float) str_replace(',', '', $r['value'])))]);
    }

    protected function reportStockByBranch(array $f): array
    {
        $rows = StockBalance::query()
            ->join('parts', 'stock_balances.part_id', '=', 'parts.id')
            ->join('branches', 'stock_balances.branch_id', '=', 'branches.id')
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where('parts.part_number', 'like', "%{$s}%"))
            ->select('branches.name as branch', 'parts.part_number', DB::raw('SUM(stock_balances.quantity) as qty'))
            ->groupBy('branches.id', 'branches.name', 'parts.id', 'parts.part_number')
            ->having('qty', '>', 0)
            ->orderBy('branch')
            ->get()
            ->map(fn ($r) => [
                'branch' => $r->branch,
                'part' => $r->part_number,
                'quantity' => number_format((float) $r->qty, 2),
            ]);

        return $this->table([
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('part', 'Part', 'القطعة'),
            $this->col('quantity', 'Quantity', 'الكمية', 'right'),
        ], $rows);
    }

    protected function reportStockByLocation(array $f): array
    {
        $rows = $this->stockBalanceQuery($f)->where('quantity', '>', 0)->get()->map(fn ($s) => [
            'branch' => $s->branch?->name,
            'location' => $s->location?->code,
            'part' => $s->part?->part_number,
            'quantity' => number_format((float) $s->quantity, 2),
        ]);

        return $this->table([
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('part', 'Part', 'القطعة'),
            $this->col('quantity', 'Quantity', 'الكمية', 'right'),
        ], $rows);
    }

    protected function reportStockCardex(array $f): array
    {
        $q = StockMovement::with(['part', 'branch', 'location'])
            ->whereBetween('movement_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['part_id'] ?? null, fn ($q, $p) => $q->where('part_id', $p))
            ->orderBy('movement_date');

        $rows = $q->get()->map(fn ($m) => [
            'date' => Carbon::parse($m->movement_date)->format('Y-m-d'),
            'part' => $m->part?->part_number,
            'type' => $m->movement_type,
            'reference' => $m->reference_no,
            'branch' => $m->branch?->name,
            'location' => $m->location?->code,
            'qty_in' => $m->quantity > 0 ? number_format($m->quantity, 2) : '',
            'qty_out' => $m->quantity < 0 ? number_format(abs($m->quantity), 2) : '',
            'balance' => number_format((float) $m->balance_after, 2),
        ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('part', 'Part', 'القطعة'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('reference', 'Reference', 'المرجع'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('qty_in', 'In', 'وارد', 'right'),
            $this->col('qty_out', 'Out', 'صادر', 'right'),
            $this->col('balance', 'Balance', 'الرصيد', 'right'),
        ], $rows);
    }

    protected function reportStockMovements(array $f): array
    {
        $q = StockMovement::with(['part', 'branch'])
            ->whereBetween('movement_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['movement_type'] ?? null, fn ($q, $t) => $q->where('movement_type', $t))
            ->latest('movement_date');

        $rows = $q->get()->map(fn ($m) => [
            'date' => Carbon::parse($m->movement_date)->format('Y-m-d H:i'),
            'part' => $m->part?->part_number,
            'type' => $m->movement_type,
            'quantity' => number_format((float) $m->quantity, 2),
            'reference' => $m->reference_no,
            'branch' => $m->branch?->name,
        ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('part', 'Part', 'القطعة'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('quantity', 'Qty', 'الكمية', 'right'),
            $this->col('reference', 'Reference', 'المرجع'),
            $this->col('branch', 'Branch', 'الفرع'),
        ], $rows);
    }

    protected function reportLowStock(array $f): array
    {
        $rows = Part::where('is_active', true)->where('min_stock', '>', 0)->get()->map(function ($part) use ($f) {
            $qty = StockBalance::where('part_id', $part->id)
                ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
                ->sum('quantity');

            if ($qty > $part->min_stock) {
                return null;
            }

            return [
                'part' => $part->part_number,
                'description' => $part->description_en,
                'on_hand' => number_format((float) $qty, 2),
                'min_stock' => number_format((float) $part->min_stock, 2),
                'shortage' => number_format(max(0, (float) $part->min_stock - (float) $qty), 2),
            ];
        })->filter()->values();

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('on_hand', 'On Hand', 'المتوفر', 'right'),
            $this->col('min_stock', 'Min Stock', 'الحد الأدنى', 'right'),
            $this->col('shortage', 'Shortage', 'النقص', 'right'),
        ], $rows);
    }

    protected function reportDeadStock(array $f): array
    {
        $cutoff = now()->subDays($f['days'])->toDateString();
        $soldPartIds = SalesInvoiceItem::query()
            ->join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoices.status', 'posted')
            ->where('sales_invoices.invoice_date', '>=', $cutoff)
            ->pluck('sales_invoice_items.part_id')
            ->unique();

        $rows = StockBalance::with('part')
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->where('quantity', '>', 0)
            ->get()
            ->filter(fn ($s) => $s->part && ! $soldPartIds->contains($s->part_id))
            ->map(fn ($s) => [
                'part' => $s->part?->part_number,
                'description' => $s->part?->description_en,
                'quantity' => number_format((float) $s->quantity, 2),
                'value' => $this->money((float) $s->quantity * (float) $s->avg_cost),
            ])
            ->unique('part')
            ->values();

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('quantity', 'Qty', 'الكمية', 'right'),
            $this->col('value', 'Value', 'القيمة', 'right'),
        ], $rows, ['note' => "No sales in last {$f['days']} days"]);
    }

    // --- Purchase reports ---

    protected function purchaseQuery(array $f)
    {
        return PurchaseInvoice::with(['vendor', 'branch'])
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b));
    }

    protected function reportPurchaseSummary(array $f): array
    {
        $invoices = $this->purchaseQuery($f)->get();
        $rows = $invoices->map(fn ($i) => [
            'invoice_no' => $i->invoice_no,
            'date' => $i->invoice_date?->format('Y-m-d'),
            'vendor' => $i->vendor?->name,
            'branch' => $i->branch?->name,
            'status' => $i->status,
            'total' => $this->money($i->total_amount),
        ]);

        return $this->table([
            $this->col('invoice_no', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows, ['total' => $this->money($invoices->where('status', 'posted')->sum('total_amount'))]);
    }

    protected function reportDailyPurchases(array $f): array
    {
        $rows = $this->purchaseQuery($f)->get()->map(fn ($i) => [
            'date' => $i->invoice_date?->format('Y-m-d'),
            'invoice_no' => $i->invoice_no,
            'vendor' => $i->vendor?->name,
            'branch' => $i->branch?->name,
            'status' => $i->status,
            'total' => $this->money($i->total_amount),
        ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('invoice_no', 'Invoice', 'الفاتورة'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportPurchasesByVendor(array $f): array
    {
        $rows = $this->purchaseQuery($f)->where('status', 'posted')
            ->select('vendor_id', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('vendor_id')
            ->with('vendor')
            ->get()
            ->map(fn ($r) => [
                'vendor' => $r->vendor?->name,
                'invoices' => $r->cnt,
                'total' => $this->money($r->total),
            ]);

        return $this->table([
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('invoices', 'Invoices', 'الفواتير', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportPendingPo(array $f): array
    {
        $rows = PurchaseOrder::with(['vendor', 'branch', 'items'])
            ->whereIn('status', ['open', 'partial'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(function ($po) {
                $pending = $po->items->sum(fn ($i) => max(0, (float) $i->quantity - (float) $i->received_qty));

                return [
                    'po_no' => $po->po_no,
                    'date' => $po->po_date?->format('Y-m-d'),
                    'vendor' => $po->vendor?->name,
                    'branch' => $po->branch?->name,
                    'status' => $po->status,
                    'pending_qty' => number_format($pending, 2),
                    'total' => $this->money($po->total_amount),
                ];
            })
            ->filter(fn ($r) => (float) str_replace(',', '', $r['pending_qty']) > 0);

        return $this->table([
            $this->col('po_no', 'PO No', 'رقم أمر الشراء'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('pending_qty', 'Pending Qty', 'كمية معلقة', 'right'),
            $this->col('total', 'PO Total', 'إجمالي الأمر', 'right'),
        ], $rows);
    }

    // --- Finance reports ---

    protected function reportTrialBalance(array $f): array
    {
        $rows = $this->accounting->trialBalance($f['from'], $f['to'])->map(fn ($r) => [
            'code' => $r['account']->account_code,
            'name' => $r['account']->name,
            'debit' => $this->money($r['debit']),
            'credit' => $this->money($r['credit']),
        ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Account', 'الحساب'),
            $this->col('debit', 'Debit', 'مدين', 'right'),
            $this->col('credit', 'Credit', 'دائن', 'right'),
        ], $rows, [
            'total_debit' => $this->money($rows->sum(fn ($r) => (float) str_replace(',', '', $r['debit']))),
            'total_credit' => $this->money($rows->sum(fn ($r) => (float) str_replace(',', '', $r['credit']))),
        ]);
    }

    protected function reportIncomeStatement(array $f): array
    {
        $data = $this->accounting->incomeStatement($f['from'], $f['to']);

        return [
            'columns' => [],
            'rows' => [],
            'summary' => [
                ['label' => 'Revenue', 'label_ar' => 'الإيرادات', 'value' => $this->money($data['revenue'])],
                ['label' => 'Expenses', 'label_ar' => 'المصروفات', 'value' => $this->money($data['expenses'])],
                ['label' => 'Net Income', 'label_ar' => 'صافي الدخل', 'value' => $this->money($data['netIncome'])],
            ],
            'meta' => ['view' => 'income_statement'],
        ];
    }

    protected function reportBalanceSheet(array $f): array
    {
        $data = $this->accounting->balanceSheet($f['as_of']);

        return [
            'columns' => [],
            'rows' => [],
            'sections' => [
                'assets' => $data['assets']->map(fn ($r) => ['name' => $r['account']->account_code.' '.$r['account']->name, 'amount' => $this->money($r['balance'])])->all(),
                'liabilities' => $data['liabilities']->map(fn ($r) => ['name' => $r['account']->account_code.' '.$r['account']->name, 'amount' => $this->money($r['balance'])])->all(),
                'equity' => $data['equity']->map(fn ($r) => ['name' => $r['account']->account_code.' '.$r['account']->name, 'amount' => $this->money($r['balance'])])->all(),
            ],
            'summary' => [
                'total_assets' => $this->money($data['totalAssets']),
                'total_liabilities' => $this->money($data['totalLiabilities']),
                'total_equity' => $this->money($data['totalEquity']),
            ],
            'meta' => ['view' => 'balance_sheet'],
        ];
    }

    protected function reportCustomerAging(array $f): array
    {
        $records = $this->accounting->customerAging();
        $rows = $records->map(fn ($r) => [
            'party' => $r['customer']?->name,
            'current' => $this->money($r['buckets']['current']),
            '31_60' => $this->money($r['buckets']['31_60']),
            '61_90' => $this->money($r['buckets']['61_90']),
            'over_90' => $this->money($r['buckets']['over_90']),
            'total' => $this->money($r['total']),
        ]);

        return $this->table([
            $this->col('party', 'Customer', 'العميل'),
            $this->col('current', '0-30', '0-30', 'right'),
            $this->col('31_60', '31-60', '31-60', 'right'),
            $this->col('61_90', '61-90', '61-90', 'right'),
            $this->col('over_90', '90+', '+90', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows, ['meta' => ['view' => 'aging']]);
    }

    protected function reportVendorAging(array $f): array
    {
        $records = $this->accounting->vendorAging();
        $rows = $records->map(fn ($r) => [
            'party' => $r['vendor']?->name,
            'current' => $this->money($r['buckets']['current']),
            '31_60' => $this->money($r['buckets']['31_60']),
            '61_90' => $this->money($r['buckets']['61_90']),
            'over_90' => $this->money($r['buckets']['over_90']),
            'total' => $this->money($r['total']),
        ]);

        return $this->table([
            $this->col('party', 'Vendor', 'المورد'),
            $this->col('current', '0-30', '0-30', 'right'),
            $this->col('31_60', '31-60', '31-60', 'right'),
            $this->col('61_90', '61-90', '61-90', 'right'),
            $this->col('over_90', '90+', '+90', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    // --- Workshop & HR ---

    protected function reportJobCards(array $f): array
    {
        $rows = JobCard::with(['customer', 'vehicle', 'mechanic', 'branch'])
            ->whereBetween('job_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->get()
            ->map(fn ($j) => [
                'job_no' => $j->job_no,
                'date' => $j->job_date?->format('Y-m-d'),
                'customer' => $j->customer?->name,
                'vehicle' => $j->vehicle?->plate_no,
                'mechanic' => $j->mechanic?->name,
                'status' => $j->status,
                'total' => $this->money($j->total_amount),
            ]);

        return $this->table([
            $this->col('job_no', 'Job No', 'رقم العمل'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('vehicle', 'Vehicle', 'المركبة'),
            $this->col('mechanic', 'Mechanic', 'الفني'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportWorkshopWip(array $f): array
    {
        $records = $this->workshop->wipReport($f['branch_id'] ? (int) $f['branch_id'] : null);
        $rows = $records->map(fn ($j) => [
            'job_no' => $j->job_no,
            'customer' => $j->customer?->name,
            'vehicle' => $j->vehicle?->plate_no,
            'mechanic' => $j->mechanic?->name,
            'promised' => $j->promised_date?->format('Y-m-d'),
            'status' => $j->status,
            'total' => $this->money($j->total_amount),
        ]);

        return $this->table([
            $this->col('job_no', 'Job No', 'رقم العمل'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('vehicle', 'Vehicle', 'المركبة'),
            $this->col('mechanic', 'Mechanic', 'الفني'),
            $this->col('promised', 'Promised', 'موعد التسليم'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows, ['wip_total' => $this->money($records->sum('total_amount'))]);
    }

    protected function reportPayrollSummary(array $f): array
    {
        $rows = PayrollRun::with('branch')
            ->where('status', 'posted')
            ->where(function ($q) use ($f) {
                $q->whereRaw('CONCAT(period_year, LPAD(period_month, 2, "0")) >= ?', [date('Ym', strtotime($f['from']))])
                    ->whereRaw('CONCAT(period_year, LPAD(period_month, 2, "0")) <= ?', [date('Ym', strtotime($f['to']))]);
            })
            ->get()
            ->map(fn ($r) => [
                'payroll_no' => $r->payroll_no,
                'period' => $r->periodLabel(),
                'branch' => $r->branch?->name ?? 'All',
                'total' => $this->money($r->total_amount),
                'posted' => $r->posted_at?->format('Y-m-d'),
            ]);

        return $this->table([
            $this->col('payroll_no', 'Payroll No', 'رقم الرواتب'),
            $this->col('period', 'Period', 'الفترة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
            $this->col('posted', 'Posted', 'تاريخ الترحيل'),
        ], $rows);
    }

    protected function reportExpiringDocuments(array $f): array
    {
        $employees = $this->hr->expiringDocuments($f['days']);
        $vehicles = $this->hr->expiringVehicles($f['days']);

        return [
            'columns' => [],
            'rows' => [],
            'employees' => $employees->map(fn ($e) => [
                'name' => $e->name,
                'aqama_no' => $e->aqama_no,
                'aqama_expiry' => $e->aqama_expiry?->format('Y-m-d'),
                'license_no' => $e->license_no,
                'license_expiry' => $e->license_expiry?->format('Y-m-d'),
            ])->all(),
            'vehicles' => $vehicles->map(fn ($v) => [
                'plate' => $v->plate_no,
                'customer' => $v->customer?->name,
                'istimara_expiry' => $v->istimara_expiry?->format('Y-m-d'),
            ])->all(),
            'meta' => ['view' => 'expiring'],
        ];
    }

    public function filterOptions(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(['id', 'part_number', 'description_en']),
            'movement_types' => StockMovement::distinct()->pluck('movement_type')->filter()->values(),
            'job_statuses' => ['open', 'in_progress', 'completed', 'invoiced', 'cancelled'],
        ];
    }
}
