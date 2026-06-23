<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashBookEntry;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\FixedAsset;
use App\Models\FixedAssetDepreciation;
use App\Models\PickTicket;
use App\Models\PosSession;
use App\Models\ProformaInvoice;
use App\Models\StockBatch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JobCard;
use App\Models\PaymentReceipt;
use App\Models\PurchaseReturn;
use App\Models\StockCountSession;
use App\Models\StockTransfer;
use App\Models\User;
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
use App\Models\TransportCashVoucher;
use App\Models\TransportDriver;
use App\Models\TransportShipment;
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
        $def = config("reports.reports.{$key}") ?? config("reports_extended.reports.{$key}");
        if (! $def) {
            throw new \InvalidArgumentException("Unknown report: {$key}");
        }

        return $def;
    }

    public function allDefinitions(): array
    {
        return array_merge(
            config('reports.reports', []),
            config('reports_extended.reports', [])
        );
    }

    public function categories(): array
    {
        return config('reports.categories', []);
    }

    public function generate(string $key, array $filters = []): array
    {
        $def = $this->definition($key);
        $locale = $filters['locale'] ?? ($def['locale'] ?? 'en');
        $filters = $this->normalizeFilters($filters, $def);

        $reportKey = $key;
        if (! empty($def['handler']) && empty($def['native'])) {
            $reportKey = $def['handler'];
            $def = $this->definition($reportKey);
        }

        $method = 'report'.str_replace(' ', '', ucwords(str_replace('-', ' ', $reportKey)));

        if (! method_exists($this, $method)) {
            throw new \RuntimeException("Report handler missing: {$method}");
        }

        $payload = $this->{$method}($filters);
        $originalDef = $this->definition($key);
        $payload['meta'] = array_merge([
            'key' => $key,
            'title' => $locale === 'ar' ? ($originalDef['title_ar'] ?? $originalDef['title']) : $originalDef['title'],
            'title_en' => $originalDef['title'],
            'title_ar' => $originalDef['title_ar'] ?? $originalDef['title'],
            'legacy' => $originalDef['legacy'] ?? null,
            'category' => $originalDef['category'],
            'view' => $payload['meta']['view'] ?? ($originalDef['view'] ?? 'table'),
            'locale' => $locale,
            'company' => $this->settings->get('company_name', config('app.name')),
            'generated_at' => now()->format('Y-m-d H:i'),
            'filters' => $filters,
            'filter_display' => $this->filterDisplay($filters, $originalDef, $locale),
            'row_count' => $this->rowCount($payload),
            'printed_by' => auth()->user()?->name,
            'alias_of' => ($reportKey !== $key) ? $reportKey : null,
        ], $payload['meta'] ?? []);

        return $payload;
    }

    protected function rowCount(array $payload): int
    {
        return count($payload['rows'] ?? [])
            + count($payload['employees'] ?? [])
            + count($payload['vehicles'] ?? []);
    }

    protected function filterDisplay(array $filters, array $def, string $locale): array
    {
        $isAr = $locale === 'ar';
        $allowed = $def['filters'] ?? [];
        $display = [];

        $labels = [
            'from' => $isAr ? __('reports.from') : __('reports.from'),
            'to' => $isAr ? __('reports.to') : __('reports.to'),
            'as_of' => __('reports.as_of'),
            'search' => __('reports.search'),
            'status' => __('reports.status'),
            'days' => __('reports.within_days'),
            'movement_type' => __('reports.movement_type'),
        ];

        foreach (['from', 'to', 'as_of', 'search', 'status', 'movement_type'] as $key) {
            if (! in_array($key, $allowed, true) || empty($filters[$key])) {
                continue;
            }
            $display[] = ['label' => $labels[$key], 'value' => $filters[$key]];
        }

        if (in_array('days', $allowed, true) && isset($filters['days'])) {
            $display[] = ['label' => $labels['days'], 'value' => (string) $filters['days']];
        }

        if (in_array('branch_id', $allowed, true)) {
            if ($filters['branch_id'] ?? null) {
                $branch = Branch::find($filters['branch_id']);
                $display[] = [
                    'label' => __('reports.branch'),
                    'value' => $branch ? ($isAr && $branch->name_ar ? $branch->name_ar : $branch->name) : (string) $filters['branch_id'],
                ];
            } else {
                $display[] = ['label' => __('reports.branch'), 'value' => __('reports.all_branches')];
            }
        }

        if (in_array('part_id', $allowed, true) && ($filters['part_id'] ?? null)) {
            $part = Part::find($filters['part_id']);
            $display[] = [
                'label' => __('reports.part'),
                'value' => $part?->part_number ?? (string) $filters['part_id'],
            ];
        }

        if (in_array('driver_id', $allowed, true) && ($filters['driver_id'] ?? null)) {
            $driver = TransportDriver::find($filters['driver_id']);
            $display[] = [
                'label' => __('reports.driver'),
                'value' => $driver?->name ?? (string) $filters['driver_id'],
            ];
        }

        return $display;
    }

    protected function normalizeFilters(array $filters, array $def): array
    {
        $allowed = $def['filters'] ?? [];
        $locale = $filters['locale'] ?? ($def['locale'] ?? 'en');
        $locale = in_array($locale, ['en', 'ar'], true) ? $locale : 'en';

        $normalized = [
            'locale' => $locale,
            'from' => $filters['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $filters['to'] ?? now()->toDateString(),
            'as_of' => $filters['as_of'] ?? now()->toDateString(),
            'branch_id' => $filters['branch_id'] ?? null,
            'part_id' => $filters['part_id'] ?? null,
            'search' => $filters['search'] ?? null,
            'movement_type' => $filters['movement_type'] ?? null,
            'status' => $filters['status'] ?? null,
            'driver_id' => $filters['driver_id'] ?? null,
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

    protected function reportSalesByUser(array $f): array
    {
        $rows = SalesInvoice::with('creator')
            ->where('status', 'posted')->whereNull('voided_at')
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->groupBy('created_by')
            ->map(fn ($invs, $userId) => [
                'user' => $invs->first()->creator?->name ?? 'System',
                'invoices' => $invs->count(),
                'total' => $this->money($invs->sum('total_amount')),
            ])->values();

        return $this->table([
            $this->col('user', 'User', 'المستخدم'),
            $this->col('invoices', 'Invoices', 'الفواتير', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportStockByFranchise(array $f): array
    {
        $query = StockBalance::with(['part.franchise', 'branch'])
            ->when($f['search'] ?? null, fn ($q, $s) => $q->whereHas('part', fn ($p) => $p->where('part_number', 'like', "%{$s}%")));

        $rows = $query->get()->groupBy(fn ($b) => $b->part?->franchise?->name ?? '—')->map(fn ($items, $franchise) => [
            'franchise' => $franchise,
            'parts' => $items->count(),
            'quantity' => number_format($items->sum('quantity'), 2),
            'value' => $this->money($items->sum(fn ($i) => (float) $i->quantity * (float) $i->avg_cost)),
        ])->values();

        return $this->table([
            $this->col('franchise', 'Franchise', 'الامتياز'),
            $this->col('parts', 'SKUs', 'الأصناف', 'right'),
            $this->col('quantity', 'Qty', 'الكمية', 'right'),
            $this->col('value', 'Value', 'القيمة', 'right'),
        ], $rows);
    }

    protected function reportPayablesReport(array $f): array
    {
        $rows = PurchaseInvoice::with('vendor')
            ->where('status', 'posted')->whereNull('voided_at')
            ->whereDate('invoice_date', '<=', $f['as_of'])
            ->get()
            ->map(fn ($inv) => [
                'vendor' => $inv->vendor?->name,
                'invoice' => $inv->invoice_no,
                'date' => $inv->invoice_date?->format('Y-m-d'),
                'outstanding' => $this->money(max(0, (float) $inv->total_amount - (float) $inv->paid_amount)),
            ])
            ->filter(fn ($r) => (float) str_replace(',', '', $r['outstanding']) > 0);

        return $this->table([
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('outstanding', 'Outstanding', 'المتبقي', 'right'),
        ], $rows);
    }

    protected function reportCashTransactions(array $f): array
    {
        $rows = PaymentReceipt::with(['customer', 'vendor'])
            ->whereBetween('receipt_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($r) => [
                'date' => $r->receipt_date?->format('Y-m-d'),
                'receipt' => $r->receipt_no,
                'party' => $r->party_type === 'customer' ? $r->customer?->name : $r->vendor?->name,
                'type' => ucfirst($r->party_type),
                'method' => $r->payment_method,
                'amount' => $this->money($r->amount),
            ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('receipt', 'Receipt', 'الإيصال'),
            $this->col('party', 'Party', 'الطرف'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('method', 'Method', 'الطريقة'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
        ], $rows);
    }

    protected function reportWipByMechanic(array $f): array
    {
        $records = $this->workshop->wipReport($f['branch_id'] ? (int) $f['branch_id'] : null);
        $rows = $records->groupBy('mechanic_id')->map(fn ($jobs, $id) => [
            'mechanic' => $jobs->first()->mechanic?->name ?? 'Unassigned',
            'jobs' => $jobs->count(),
            'total' => $this->money($jobs->sum('total_amount')),
        ])->values();

        return $this->table([
            $this->col('mechanic', 'Mechanic', 'الفني'),
            $this->col('jobs', 'Open Jobs', 'الأعمال', 'right'),
            $this->col('total', 'WIP Value', 'القيمة', 'right'),
        ], $rows);
    }

    protected function reportManufacturerCodes(array $f): array
    {
        $query = Part::with('brand')->whereNotNull('manufacturer_part_no');
        if ($f['search'] ?? null) {
            $query->where(fn ($q) => $q->where('manufacturer_part_no', 'like', "%{$f['search']}%")->orWhere('part_number', 'like', "%{$f['search']}%"));
        }

        $rows = $query->orderBy('manufacturer_part_no')->get()->map(fn ($p) => [
            'part' => $p->part_number,
            'mfr_code' => $p->manufacturer_part_no,
            'oem' => $p->oem_no,
            'brand' => $p->brand?->name,
            'description' => $f['locale'] === 'ar' && $p->description_ar ? $p->description_ar : $p->description_en,
        ]);

        return $this->table([
            $this->col('part', 'Part No', 'رقم القطعة'),
            $this->col('mfr_code', 'Mfr Code', 'رمز المصنع'),
            $this->col('oem', 'OEM', 'OEM'),
            $this->col('brand', 'Brand', 'العلامة'),
            $this->col('description', 'Description', 'الوصف'),
        ], $rows);
    }

    protected function reportPhysicalInventory(array $f): array
    {
        $rows = StockCountSession::with('branch')
            ->whereBetween('count_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($s) => [
                'count_no' => $s->count_no,
                'date' => $s->count_date?->format('Y-m-d'),
                'branch' => $s->branch?->name,
                'status' => $s->status,
                'items' => $s->items()->count(),
            ]);

        return $this->table([
            $this->col('count_no', 'Count No', 'رقم الجرد'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('items', 'Lines', 'البنود', 'right'),
        ], $rows);
    }

    protected function reportPurchaseReturns(array $f): array
    {
        $rows = PurchaseReturn::with(['vendor', 'branch'])
            ->where('status', 'posted')
            ->whereBetween('return_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($r) => [
                'return_no' => $r->return_no,
                'date' => $r->return_date?->format('Y-m-d'),
                'vendor' => $r->vendor?->name,
                'branch' => $r->branch?->name,
                'total' => $this->money($r->total_amount),
            ]);

        return $this->table([
            $this->col('return_no', 'Return No', 'رقم المرتجع'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportPaymentReceipts(array $f): array
    {
        return $this->reportCashTransactions($f);
    }

    protected function reportCustomerStatementReport(array $f): array
    {
        $rows = Customer::where('is_active', true)->get()->map(function ($c) use ($f) {
            $charges = (float) SalesInvoice::where('customer_id', $c->id)->where('status', 'posted')
                ->whereNull('voided_at')->whereBetween('invoice_date', [$f['from'], $f['to']])->sum('total_amount');
            $payments = (float) PaymentReceipt::where('customer_id', $c->id)->where('party_type', 'customer')
                ->whereBetween('receipt_date', [$f['from'], $f['to']])->sum('amount');

            return [
                'customer' => $f['locale'] === 'ar' && $c->name_ar ? $c->name_ar : $c->name,
                'charges' => $this->money($charges),
                'payments' => $this->money($payments),
                'balance' => $this->money($charges - $payments),
            ];
        })->filter(fn ($r) => $r['charges'] !== '0.00' || $r['payments'] !== '0.00');

        return $this->table([
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('charges', 'Charges', 'المستحق', 'right'),
            $this->col('payments', 'Payments', 'المدفوع', 'right'),
            $this->col('balance', 'Net', 'الصافي', 'right'),
        ], $rows);
    }

    protected function reportVendorStatementReport(array $f): array
    {
        $rows = Vendor::get()->map(function ($v) use ($f) {
            $purchases = (float) PurchaseInvoice::where('vendor_id', $v->id)->where('status', 'posted')
                ->whereNull('voided_at')->whereBetween('invoice_date', [$f['from'], $f['to']])->sum('total_amount');
            $payments = (float) PaymentReceipt::where('vendor_id', $v->id)->where('party_type', 'vendor')
                ->whereBetween('receipt_date', [$f['from'], $f['to']])->sum('amount');

            return [
                'vendor' => $v->name,
                'purchases' => $this->money($purchases),
                'payments' => $this->money($payments),
                'balance' => $this->money($purchases - $payments),
            ];
        })->filter(fn ($r) => $r['purchases'] !== '0.00' || $r['payments'] !== '0.00');

        return $this->table([
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('purchases', 'Purchases', 'المشتريات', 'right'),
            $this->col('payments', 'Payments', 'المدفوعات', 'right'),
            $this->col('balance', 'Net', 'الصافي', 'right'),
        ], $rows);
    }

    protected function reportStockValuation(array $f): array
    {
        $query = StockBalance::with(['part', 'branch', 'location'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b));

        $rows = $query->get()->map(fn ($b) => [
            'part' => $b->part?->part_number,
            'branch' => $b->branch?->code,
            'location' => $b->location?->code,
            'qty' => number_format($b->quantity, 2),
            'avg_cost' => $this->money($b->avg_cost),
            'value' => $this->money((float) $b->quantity * (float) $b->avg_cost),
        ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
            $this->col('avg_cost', 'Avg Cost', 'متوسط التكلفة', 'right'),
            $this->col('value', 'Value', 'القيمة', 'right'),
        ], $rows, ['total_value' => $this->money($query->get()->sum(fn ($b) => (float) $b->quantity * (float) $b->avg_cost))]);
    }

    protected function reportSalesByPart(array $f): array
    {
        $rows = SalesInvoiceItem::query()
            ->whereHas('salesInvoice', fn ($q) => $q->where('status', 'posted')->whereNull('voided_at')
                ->whereBetween('invoice_date', [$f['from'], $f['to']])
                ->when($f['branch_id'] ?? null, fn ($q2, $b) => $q2->where('branch_id', $b)))
            ->with('part')
            ->get()
            ->groupBy('part_id')
            ->map(fn ($items) => [
                'part' => $items->first()->part?->part_number,
                'description' => $items->first()->part?->description_en,
                'qty' => number_format($items->sum('quantity'), 2),
                'total' => $this->money($items->sum('line_total')),
            ])->sortByDesc(fn ($r) => (float) str_replace(',', '', $r['total']))->values();

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('qty', 'Qty Sold', 'الكمية', 'right'),
            $this->col('total', 'Revenue', 'الإيراد', 'right'),
        ], $rows);
    }

    protected function reportProfitMargin(array $f): array
    {
        $rows = SalesInvoiceItem::query()
            ->whereHas('salesInvoice', fn ($q) => $q->where('status', 'posted')->whereNull('voided_at')
                ->whereBetween('invoice_date', [$f['from'], $f['to']])
                ->when($f['branch_id'] ?? null, fn ($q2, $b) => $q2->where('branch_id', $b)))
            ->with('part')
            ->get()
            ->groupBy('part_id')
            ->map(function ($items) {
                $revenue = $items->sum('line_total');
                $cost = $items->sum(fn ($i) => (float) $i->quantity * ((float) $i->unit_cost ?: (float) ($i->part?->cost_price ?? 0)));
                $margin = $revenue - $cost;

                return [
                    'part' => $items->first()->part?->part_number,
                    'revenue' => $this->money($revenue),
                    'cost' => $this->money($cost),
                    'margin' => $this->money($margin),
                    'pct' => $revenue > 0 ? round(($margin / $revenue) * 100, 1).'%' : '0%',
                ];
            })->values();

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('revenue', 'Revenue', 'الإيراد', 'right'),
            $this->col('cost', 'Cost', 'التكلفة', 'right'),
            $this->col('margin', 'Margin', 'الهامش', 'right'),
            $this->col('pct', '%', 'النسبة', 'right'),
        ], $rows);
    }

    protected function reportBranchPerformance(array $f): array
    {
        $rows = Branch::where('is_active', true)->get()->map(function ($b) use ($f) {
            $sales = (float) SalesInvoice::where('branch_id', $b->id)->where('status', 'posted')
                ->whereNull('voided_at')->whereBetween('invoice_date', [$f['from'], $f['to']])->sum('total_amount');
            $purchases = (float) PurchaseInvoice::where('branch_id', $b->id)->where('status', 'posted')
                ->whereNull('voided_at')->whereBetween('invoice_date', [$f['from'], $f['to']])->sum('total_amount');

            return [
                'branch' => $b->name,
                'sales' => $this->money($sales),
                'purchases' => $this->money($purchases),
                'net' => $this->money($sales - $purchases),
            ];
        });

        return $this->table([
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('sales', 'Sales', 'المبيعات', 'right'),
            $this->col('purchases', 'Purchases', 'المشتريات', 'right'),
            $this->col('net', 'Net', 'الصافي', 'right'),
        ], $rows);
    }

    protected function reportOpenQuotations(array $f): array
    {
        $rows = Quotation::with(['customer', 'branch'])
            ->whereNotIn('status', ['converted', 'cancelled'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($q) => [
                'quote_no' => $q->quotation_no,
                'date' => $q->quotation_date?->format('Y-m-d'),
                'customer' => $q->customer?->name,
                'branch' => $q->branch?->name,
                'total' => $this->money($q->total_amount),
                'status' => $q->status,
            ]);

        return $this->table([
            $this->col('quote_no', 'Quote No', 'رقم العرض'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
            $this->col('status', 'Status', 'الحالة'),
        ], $rows);
    }

    protected function reportStockTransfersReport(array $f): array
    {
        $rows = StockTransfer::with(['fromBranch', 'toBranch'])
            ->whereBetween('transfer_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where(fn ($q2) => $q2->where('from_branch_id', $b)->orWhere('to_branch_id', $b)))
            ->get()
            ->map(fn ($t) => [
                'transfer_no' => $t->transfer_no,
                'date' => $t->transfer_date?->format('Y-m-d'),
                'from' => $t->fromBranch?->code,
                'to' => $t->toBranch?->code,
                'status' => $t->status,
                'lines' => $t->items()->count(),
            ]);

        return $this->table([
            $this->col('transfer_no', 'Transfer No', 'رقم التحويل'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('from', 'From', 'من'),
            $this->col('to', 'To', 'إلى'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('lines', 'Lines', 'البنود', 'right'),
        ], $rows);
    }

    protected function reportJournalEntriesReport(array $f): array
    {
        $rows = JournalEntry::with('branch')
            ->where('status', 'posted')
            ->whereBetween('entry_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($e) => [
                'entry_no' => $e->entry_no,
                'date' => $e->entry_date?->format('Y-m-d'),
                'type' => $e->entry_type ?? 'auto',
                'branch' => $e->branch?->code,
                'description' => \Illuminate\Support\Str::limit($e->description, 40),
            ]);

        return $this->table([
            $this->col('entry_no', 'Entry No', 'رقم القيد'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('description', 'Description', 'الوصف'),
        ], $rows);
    }

    protected function reportGlDetail(array $f): array
    {
        $rows = JournalEntryLine::with(['journalEntry', 'account'])
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                ->whereBetween('entry_date', [$f['from'], $f['to']]))
            ->get()
            ->map(fn ($l) => [
                'date' => $l->journalEntry?->entry_date?->format('Y-m-d'),
                'entry' => $l->journalEntry?->entry_no,
                'account' => $l->account?->account_code.' '.$l->account?->name,
                'debit' => $this->money($l->debit),
                'credit' => $this->money($l->credit),
            ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('entry', 'Entry', 'القيد'),
            $this->col('account', 'Account', 'الحساب'),
            $this->col('debit', 'Debit', 'مدين', 'right'),
            $this->col('credit', 'Credit', 'دائن', 'right'),
        ], $rows);
    }

    protected function reportInvoiceRegisterSales(array $f): array
    {
        $rows = SalesInvoice::with(['customer', 'branch'])
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($i) => [
                'invoice' => $i->invoice_no,
                'date' => $i->invoice_date?->format('Y-m-d'),
                'customer' => $i->customer?->name,
                'branch' => $i->branch?->code,
                'status' => $i->voided_at ? 'voided' : $i->status,
                'total' => $this->money($i->total_amount),
            ]);

        return $this->table([
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportInvoiceRegisterPurchase(array $f): array
    {
        $rows = PurchaseInvoice::with(['vendor', 'branch'])
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($i) => [
                'invoice' => $i->invoice_no,
                'date' => $i->invoice_date?->format('Y-m-d'),
                'vendor' => $i->vendor?->name,
                'branch' => $i->branch?->code,
                'status' => $i->voided_at ? 'voided' : $i->status,
                'total' => $this->money($i->total_amount),
            ]);

        return $this->table([
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportPartsWithoutMovement(array $f): array
    {
        $since = now()->subDays($f['days'])->toDateString();
        $movedPartIds = StockMovement::where('movement_date', '>=', $since)->distinct()->pluck('part_id');

        $rows = Part::where('is_active', true)->whereNotIn('id', $movedPartIds)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->whereHas('stockBalances', fn ($s) => $s->where('branch_id', $b)->where('quantity', '>', 0)))
            ->orderBy('part_number')
            ->get()
            ->map(fn ($p) => [
                'part' => $p->part_number,
                'description' => $f['locale'] === 'ar' && $p->description_ar ? $p->description_ar : $p->description_en,
                'cost' => $this->money($p->cost_price),
                'list' => $this->money($p->list_price),
            ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('cost', 'Cost', 'التكلفة', 'right'),
            $this->col('list', 'List', 'القائمة', 'right'),
        ], $rows);
    }

    protected function reportReservedStock(array $f): array
    {
        $query = StockBalance::with(['part', 'branch', 'location'])
            ->where('reserved_qty', '>', 0)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b));

        $rows = $query->get()->map(fn ($b) => [
            'part' => $b->part?->part_number,
            'branch' => $b->branch?->code,
            'location' => $b->location?->code,
            'on_hand' => number_format($b->quantity, 2),
            'reserved' => number_format($b->reserved_qty, 2),
            'available' => number_format((float) $b->quantity - (float) $b->reserved_qty, 2),
        ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('on_hand', 'On Hand', 'المتوفر', 'right'),
            $this->col('reserved', 'Reserved', 'محجوز', 'right'),
            $this->col('available', 'Available', 'متاح', 'right'),
        ], $rows);
    }

    protected function reportCostVsListPrice(array $f): array
    {
        $query = Part::where('is_active', true);
        if ($f['search'] ?? null) {
            $query->where('part_number', 'like', "%{$f['search']}%");
        }

        $rows = $query->orderBy('part_number')->get()->map(fn ($p) => [
            'part' => $p->part_number,
            'cost' => $this->money($p->cost_price),
            'list' => $this->money($p->list_price),
            'markup' => (float) $p->cost_price > 0
                ? round((((float) $p->list_price - (float) $p->cost_price) / (float) $p->cost_price) * 100, 1).'%'
                : '—',
        ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('cost', 'Cost', 'التكلفة', 'right'),
            $this->col('list', 'List Price', 'سعر القائمة', 'right'),
            $this->col('markup', 'Markup', 'الهامش', 'right'),
        ], $rows);
    }

    protected function reportPartsMasterAr(array $f): array
    {
        $f['locale'] = 'ar';

        return $this->reportPartsMaster($f);
    }

    protected function reportBatchStockList(array $f): array
    {
        $rows = StockBatch::with(['part', 'branch', 'location'])
            ->where('quantity', '>', 0)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['part_id'] ?? null, fn ($q, $p) => $q->where('part_id', $p))
            ->get()
            ->map(fn ($b) => [
                'part' => $b->part?->part_number,
                'batch' => $b->batch_no ?? $b->lot_no ?? '—',
                'branch' => $b->branch?->code,
                'location' => $b->location?->code,
                'qty' => number_format($b->quantity, 2),
                'cost' => $this->money($b->unit_cost),
            ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('batch', 'Batch', 'الدفعة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
            $this->col('cost', 'Cost', 'التكلفة', 'right'),
        ], $rows);
    }

    protected function reportFixedAssetRegister(array $f): array
    {
        $rows = FixedAsset::with(['category', 'branch'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->orderBy('asset_code')
            ->get()
            ->map(fn ($a) => [
                'code' => $a->asset_code,
                'name' => $a->name,
                'category' => $a->category?->name,
                'purchase' => $this->money($a->purchase_value),
                'nbv' => $this->money($a->net_book_value),
                'status' => $a->status,
            ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Name', 'الاسم'),
            $this->col('category', 'Category', 'الفئة'),
            $this->col('purchase', 'Purchase', 'قيمة الشراء', 'right'),
            $this->col('nbv', 'NBV', 'القيمة الدفترية', 'right'),
            $this->col('status', 'Status', 'الحالة'),
        ], $rows);
    }

    protected function reportProformaList(array $f): array
    {
        $rows = ProformaInvoice::with(['customer', 'branch'])
            ->whereBetween('proforma_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($p) => [
                'proforma' => $p->proforma_no,
                'date' => $p->proforma_date?->format('Y-m-d'),
                'customer' => $p->customer?->name,
                'branch' => $p->branch?->code,
                'status' => $p->status,
                'total' => $this->money($p->total_amount),
            ]);

        return $this->table([
            $this->col('proforma', 'Proforma', 'الفاتورة الأولية'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportPickTicketList(array $f): array
    {
        $rows = PickTicket::with(['salesInvoice.customer', 'branch'])
            ->whereBetween('created_at', [$f['from'].' 00:00:00', $f['to'].' 23:59:59'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->get()
            ->map(fn ($t) => [
                'pick' => $t->pick_no,
                'invoice' => $t->salesInvoice?->invoice_no,
                'customer' => $t->salesInvoice?->customer?->name,
                'branch' => $t->branch?->code,
                'status' => $t->status,
                'picked' => $t->picked_at?->format('Y-m-d H:i') ?? '—',
            ]);

        return $this->table([
            $this->col('pick', 'Pick No', 'رقم الصرف'),
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('picked', 'Picked At', 'تاريخ الصرف'),
        ], $rows);
    }

    protected function reportPosSales(array $f): array
    {
        $rows = SalesInvoice::with(['customer', 'branch'])
            ->where('source', 'pos')
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($i) => [
                'invoice' => $i->invoice_no,
                'date' => $i->invoice_date?->format('Y-m-d'),
                'customer' => $i->customer?->name,
                'branch' => $i->branch?->code,
                'total' => $this->money($i->total_amount),
            ]);

        return $this->table([
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportCashBookRegister(array $f): array
    {
        $rows = CashBookEntry::with(['account', 'branch'])
            ->whereBetween('entry_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->orderBy('entry_date')
            ->get()
            ->map(fn ($e) => [
                'entry' => $e->entry_no,
                'date' => $e->entry_date?->format('Y-m-d'),
                'type' => $e->entry_type,
                'account' => $e->account?->name,
                'amount' => $this->money($e->amount),
                'balance' => $this->money($e->running_balance),
            ]);

        return $this->table([
            $this->col('entry', 'Entry', 'القيد'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('type', 'Type', 'النوع'),
            $this->col('account', 'Account', 'الحساب'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
            $this->col('balance', 'Balance', 'الرصيد', 'right'),
        ], $rows);
    }

    protected function reportCashFlowStatement(array $f): array
    {
        $receipts = (float) CashBookEntry::whereBetween('entry_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->whereIn('entry_type', ['receipt', 'in'])
            ->sum('amount');

        $payments = (float) CashBookEntry::whereBetween('entry_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->whereIn('entry_type', ['payment', 'out'])
            ->sum('amount');

        $rows = collect([
            ['line' => 'Cash Receipts', 'amount' => $this->money($receipts)],
            ['line' => 'Cash Payments', 'amount' => $this->money($payments)],
            ['line' => 'Net Cash Flow', 'amount' => $this->money($receipts - $payments)],
        ]);

        return $this->table([
            $this->col('line', 'Description', 'الوصف'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
        ], $rows);
    }

    protected function reportAuditLogReport(array $f): array
    {
        $rows = AuditLog::with('user')
            ->whereBetween('created_at', [$f['from'].' 00:00:00', $f['to'].' 23:59:59'])
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn ($l) => [
                'time' => $l->created_at?->format('Y-m-d H:i'),
                'user' => $l->user?->name ?? '—',
                'action' => $l->action,
                'document' => $l->document_no ?? '—',
            ]);

        return $this->table([
            $this->col('time', 'Time', 'الوقت'),
            $this->col('user', 'User', 'المستخدم'),
            $this->col('action', 'Action', 'الإجراء'),
            $this->col('document', 'Document', 'المستند'),
        ], $rows);
    }

    protected function reportShowroomStock(array $f): array
    {
        return $this->locationTypeStock($f, 'showroom');
    }

    protected function reportWorkshopStock(array $f): array
    {
        return $this->locationTypeStock($f, 'workshop');
    }

    protected function locationTypeStock(array $f, string $type): array
    {
        $rows = StockBalance::with(['part', 'branch', 'location'])
            ->whereHas('location', fn ($q) => $q->where('location_type', $type))
            ->where('quantity', '>', 0)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['search'] ?? null, fn ($q, $s) => $q->whereHas('part', fn ($p) => $p->where('part_number', 'like', "%{$s}%")))
            ->get()
            ->map(fn ($b) => [
                'part' => $b->part?->part_number,
                'branch' => $b->branch?->code,
                'location' => $b->location?->code,
                'qty' => number_format($b->quantity, 2),
            ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('location', 'Location', 'الموقع'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
        ], $rows);
    }

    protected function reportCurrencyList(array $f): array
    {
        $rows = Currency::orderBy('code')->get()->map(fn ($c) => [
            'code' => $c->code,
            'name' => $c->name,
            'symbol' => $c->symbol,
            'rate' => number_format($c->exchange_rate, 4),
            'base' => $c->is_base ? 'Yes' : 'No',
            'active' => $c->is_active ? 'Yes' : 'No',
        ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Name', 'الاسم'),
            $this->col('symbol', 'Symbol', 'الرمز'),
            $this->col('rate', 'Rate', 'السعر', 'right'),
            $this->col('base', 'Base', 'أساسي'),
            $this->col('active', 'Active', 'نشط'),
        ], $rows);
    }

    protected function reportForeignPurchases(array $f): array
    {
        $rows = PurchaseInvoice::with(['vendor', 'branch'])
            ->whereNotNull('currency_id')
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($i) => [
                'invoice' => $i->invoice_no,
                'vendor' => $i->vendor?->name,
                'date' => $i->invoice_date?->format('Y-m-d'),
                'foreign' => $this->money($i->foreign_total ?? $i->total_amount),
                'base' => $this->money($i->total_amount),
            ]);

        return $this->table([
            $this->col('invoice', 'Invoice', 'الفاتورة'),
            $this->col('vendor', 'Vendor', 'المورد'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('foreign', 'Foreign', 'أجنبي', 'right'),
            $this->col('base', 'Base', 'محلي', 'right'),
        ], $rows);
    }

    protected function reportBatchExpiry(array $f): array
    {
        $until = now()->addDays($f['days'])->toDateString();

        $rows = StockBatch::with(['part', 'location'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $until)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($b) => [
                'part' => $b->part?->part_number,
                'batch' => $b->batch_no ?? '—',
                'qty' => number_format($b->quantity, 2),
                'expiry' => $b->expiry_date?->format('Y-m-d'),
            ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('batch', 'Batch', 'الدفعة'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
            $this->col('expiry', 'Expiry', 'الانتهاء'),
        ], $rows);
    }

    protected function reportSerialTracker(array $f): array
    {
        $rows = StockBatch::with(['part', 'branch'])
            ->whereNotNull('serial_no')
            ->where('quantity', '>', 0)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where('serial_no', 'like', "%{$s}%"))
            ->get()
            ->map(fn ($b) => [
                'serial' => $b->serial_no,
                'part' => $b->part?->part_number,
                'branch' => $b->branch?->code,
                'qty' => number_format($b->quantity, 2),
            ]);

        return $this->table([
            $this->col('serial', 'Serial', 'التسلسلي'),
            $this->col('part', 'Part', 'القطعة'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
        ], $rows);
    }

    protected function reportGlBatchPosting(array $f): array
    {
        $rows = JournalEntry::with('lines')
            ->whereBetween('entry_date', [$f['from'], $f['to']])
            ->where('status', 'posted')
            ->get()
            ->map(fn ($j) => [
                'entry' => $j->entry_no,
                'date' => $j->entry_date?->format('Y-m-d'),
                'description' => $j->description,
                'lines' => $j->lines->count(),
                'total' => $this->money($j->lines->sum('debit')),
            ]);

        return $this->table([
            $this->col('entry', 'Entry', 'القيد'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('lines', 'Lines', 'البنود', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportUserActivityLog(array $f): array
    {
        return $this->reportAuditLogReport($f);
    }

    protected function reportPosSessionSummary(array $f): array
    {
        $rows = PosSession::with(['posTerminal', 'user', 'branch'])
            ->whereBetween('opened_at', [$f['from'].' 00:00:00', $f['to'].' 23:59:59'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($s) => [
                'session' => $s->session_no,
                'terminal' => $s->posTerminal?->code,
                'user' => $s->user?->name,
                'opened' => $s->opened_at?->format('Y-m-d H:i'),
                'sales' => $this->money($s->total_sales),
                'status' => $s->status,
            ]);

        return $this->table([
            $this->col('session', 'Session', 'الجلسة'),
            $this->col('terminal', 'Terminal', 'الجهاز'),
            $this->col('user', 'User', 'المستخدم'),
            $this->col('opened', 'Opened', 'الفتح'),
            $this->col('sales', 'Sales', 'المبيعات', 'right'),
            $this->col('status', 'Status', 'الحالة'),
        ], $rows);
    }

    protected function reportFixedAssetDepreciation(array $f): array
    {
        $rows = FixedAssetDepreciation::with('fixedAsset')
            ->whereHas('fixedAsset')
            ->get()
            ->filter(function ($d) use ($f) {
                $date = sprintf('%04d-%02d-01', $d->dep_year, $d->dep_month);

                return $date >= $f['from'] && $date <= $f['to'];
            })
            ->map(fn ($d) => [
                'asset' => $d->fixedAsset?->asset_code,
                'period' => $d->dep_year.'-'.str_pad((string) $d->dep_month, 2, '0', STR_PAD_LEFT),
                'amount' => $this->money($d->amount),
            ]);

        return $this->table([
            $this->col('asset', 'Asset', 'الأصل'),
            $this->col('period', 'Period', 'الفترة'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
        ], $rows);
    }

    protected function reportCashBookBalance(array $f): array
    {
        $rows = Branch::when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('id', $b))
            ->where('is_active', true)
            ->get()
            ->map(function ($branch) use ($f) {
                $balance = CashBookEntry::where('branch_id', $branch->id)
                    ->where('entry_date', '<=', $f['as_of'])
                    ->orderByDesc('entry_date')
                    ->orderByDesc('id')
                    ->value('running_balance') ?? 0;

                return [
                    'branch' => $branch->name,
                    'as_of' => $f['as_of'],
                    'balance' => $this->money($balance),
                ];
            });

        return $this->table([
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('as_of', 'As Of', 'حتى'),
            $this->col('balance', 'Balance', 'الرصيد', 'right'),
        ], $rows);
    }

    protected function reportPickTicketPending(array $f): array
    {
        $f['status'] = 'open';

        return $this->reportPickTicketList($f);
    }

    protected function reportProformaOpen(array $f): array
    {
        $rows = ProformaInvoice::with(['customer', 'branch'])
            ->where('status', '!=', 'converted')
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($p) => [
                'proforma' => $p->proforma_no,
                'customer' => $p->customer?->name,
                'date' => $p->proforma_date?->format('Y-m-d'),
                'total' => $this->money($p->total_amount),
            ]);

        return $this->table([
            $this->col('proforma', 'Proforma', 'الفاتورة'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportCurrencyRateHistory(array $f): array
    {
        $rows = CurrencyRate::with('currency')
            ->whereBetween('rate_date', [$f['from'], $f['to']])
            ->orderByDesc('rate_date')
            ->get()
            ->map(fn ($r) => [
                'currency' => $r->currency?->code,
                'date' => $r->rate_date?->format('Y-m-d'),
                'rate' => number_format($r->exchange_rate, 6),
            ]);

        return $this->table([
            $this->col('currency', 'Currency', 'العملة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('rate', 'Rate', 'السعر', 'right'),
        ], $rows);
    }

    protected function reportBatchValuation(array $f): array
    {
        $rows = StockBatch::with(['part', 'branch'])
            ->where('quantity', '>', 0)
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($b) => [
                'part' => $b->part?->part_number,
                'batch' => $b->batch_no ?? '—',
                'qty' => number_format($b->quantity, 2),
                'value' => $this->money((float) $b->quantity * (float) $b->unit_cost),
            ]);

        return $this->table([
            $this->col('part', 'Part', 'القطعة'),
            $this->col('batch', 'Batch', 'الدفعة'),
            $this->col('qty', 'Qty', 'الكمية', 'right'),
            $this->col('value', 'Value', 'القيمة', 'right'),
        ], $rows);
    }

    protected function reportPosDailySales(array $f): array
    {
        $rows = SalesInvoice::where('source', 'pos')
            ->whereBetween('invoice_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->selectRaw('invoice_date, COUNT(*) as invoices, SUM(total_amount) as total')
            ->groupBy('invoice_date')
            ->orderBy('invoice_date')
            ->get()
            ->map(fn ($r) => [
                'date' => $r->invoice_date,
                'invoices' => $r->invoices,
                'total' => $this->money($r->total),
            ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('invoices', 'Invoices', 'الفواتير', 'right'),
            $this->col('total', 'Total', 'الإجمالي', 'right'),
        ], $rows);
    }

    protected function reportAssetDisposalRegister(array $f): array
    {
        $rows = FixedAsset::where('status', 'disposed')
            ->whereBetween('disposed_at', [$f['from'], $f['to']])
            ->get()
            ->map(fn ($a) => [
                'code' => $a->asset_code,
                'name' => $a->name,
                'disposed' => $a->disposed_at?->format('Y-m-d'),
                'nbv' => $this->money($a->net_book_value),
            ]);

        return $this->table([
            $this->col('code', 'Code', 'الرمز'),
            $this->col('name', 'Name', 'الاسم'),
            $this->col('disposed', 'Disposed', 'التخلص'),
            $this->col('nbv', 'NBV', 'القيمة الدفترية', 'right'),
        ], $rows);
    }

    protected function reportShowroomVehicleStock(array $f): array
    {
        $rows = \App\Models\ShowroomVehicle::with(['branch', 'model', 'color'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->whereIn('status', ['in_stock', 'in_transit'])
            ->orderBy('stock_no')
            ->get()
            ->map(fn ($v) => [
                'stock' => $v->stock_no,
                'chassis' => $v->chassis_no,
                'model' => localized($v->model),
                'color' => localized($v->color),
                'branch' => localized($v->branch),
                'status' => $v->status,
                'price' => $this->money($v->list_price),
            ]);

        return $this->table([
            $this->col('stock', 'Stock No', 'رقم المخزون'),
            $this->col('chassis', 'Chassis', 'الشassis'),
            $this->col('model', 'Model', 'الموديل'),
            $this->col('color', 'Color', 'اللون'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('price', 'List Price', 'سعر البيع', 'right'),
        ], $rows);
    }

    protected function reportShowroomVehicleSales(array $f): array
    {
        $rows = \App\Models\ShowroomVehicle::with(['customer', 'branch', 'model'])
            ->where('status', 'sold')
            ->whereBetween('sold_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->get()
            ->map(fn ($v) => [
                'stock' => $v->stock_no,
                'chassis' => $v->chassis_no,
                'customer' => localized($v->customer),
                'sold' => $v->sold_date?->format('Y-m-d'),
                'price' => $this->money($v->list_price),
            ]);

        return $this->table([
            $this->col('stock', 'Stock No', 'رقم المخزون'),
            $this->col('chassis', 'Chassis', 'الشassis'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('sold', 'Sold Date', 'تاريخ البيع'),
            $this->col('price', 'Price', 'السعر', 'right'),
        ], $rows);
    }

    protected function reportChassisTracker(array $f): array
    {
        $rows = \App\Models\ShowroomVehicle::with(['branch', 'model'])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where('chassis_no', 'like', "%{$s}%"))
            ->orderBy('chassis_no')
            ->get()
            ->map(fn ($v) => [
                'chassis' => $v->chassis_no,
                'engine' => $v->engine_no ?? '—',
                'model' => localized($v->model),
                'branch' => localized($v->branch),
                'status' => $v->status,
            ]);

        return $this->table([
            $this->col('chassis', 'Chassis', 'الشassis'),
            $this->col('engine', 'Engine', 'المحرك'),
            $this->col('model', 'Model', 'الموديل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('status', 'Status', 'الحالة'),
        ], $rows);
    }

    protected function reportShowroomTransfer(array $f): array
    {
        $rows = \App\Models\ShowroomVehicleTransfer::with(['vehicle', 'fromBranch', 'toBranch'])
            ->whereBetween('transfer_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where(function ($q) use ($b) {
                $q->where('from_branch_id', $b)->orWhere('to_branch_id', $b);
            }))
            ->orderByDesc('transfer_date')
            ->get()
            ->map(fn ($t) => [
                'transfer' => $t->transfer_no,
                'vehicle' => $t->vehicle?->chassis_no,
                'from' => $t->fromBranch?->name,
                'to' => $t->toBranch?->name,
                'date' => $t->transfer_date?->format('Y-m-d'),
                'status' => $t->status,
            ]);

        return $this->table([
            $this->col('transfer', 'Transfer No', 'رقم التحويل'),
            $this->col('vehicle', 'Chassis', 'الشassis'),
            $this->col('from', 'From', 'من'),
            $this->col('to', 'To', 'إلى'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('status', 'Status', 'الحالة'),
        ], $rows);
    }

    protected function reportTransportShipmentsReport(array $f): array
    {
        $rows = TransportShipment::with(['customer', 'branch', 'driver'])
            ->whereBetween('ship_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['driver_id'] ?? null, fn ($q, $d) => $q->where('transport_driver_id', $d))
            ->orderBy('ship_date')
            ->get()
            ->map(fn ($s) => [
                'shipment' => $s->shipment_no,
                'date' => $s->ship_date?->format('Y-m-d'),
                'customer' => $s->customer?->name,
                'driver' => $s->driver?->name ?? '—',
                'status' => $s->status,
                'charge' => $this->money($s->transport_charge),
                'cod' => $this->money($s->cod_amount),
            ]);

        return $this->table([
            $this->col('shipment', 'Shipment', 'الشحنة'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('driver', 'Driver', 'السائق'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('charge', 'Charge', 'الرسوم', 'right'),
            $this->col('cod', 'COD', 'COD', 'right'),
        ], $rows);
    }

    protected function reportTransportDriverReport(array $f): array
    {
        $rows = TransportShipment::with(['customer', 'driver'])
            ->whereBetween('ship_date', [$f['from'], $f['to']])
            ->when($f['driver_id'] ?? null, fn ($q, $d) => $q->where('transport_driver_id', $d))
            ->orderBy('transport_driver_id')
            ->orderBy('ship_date')
            ->get()
            ->groupBy('transport_driver_id')
            ->flatMap(function ($group) {
                $driver = $group->first()->driver;

                return $group->map(fn ($s) => [
                    'driver' => $driver?->name ?? '—',
                    'shipment' => $s->shipment_no,
                    'customer' => $s->customer?->name,
                    'date' => $s->ship_date?->format('Y-m-d'),
                    'status' => $s->status,
                    'cod' => $this->money($s->cod_amount),
                    'collected' => $this->money($s->cod_collected),
                ]);
            });

        return $this->table([
            $this->col('driver', 'Driver', 'السائق'),
            $this->col('shipment', 'Shipment', 'الشحنة'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('cod', 'COD', 'COD', 'right'),
            $this->col('collected', 'Collected', 'المحصّل', 'right'),
        ], $rows);
    }

    protected function reportTransportCashReport(array $f): array
    {
        $rows = TransportCashVoucher::with(['driver', 'branch'])
            ->where('status', 'posted')
            ->whereBetween('voucher_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->orderBy('voucher_date')
            ->get()
            ->map(fn ($v) => [
                'voucher' => $v->voucher_no,
                'date' => $v->voucher_date?->format('Y-m-d'),
                'driver' => $v->driver?->name,
                'branch' => $v->branch?->code,
                'amount' => $this->money($v->total_amount),
            ]);

        return $this->table([
            $this->col('voucher', 'Voucher', 'السند'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('driver', 'Driver', 'السائق'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('amount', 'Amount', 'المبلغ', 'right'),
        ], $rows);
    }

    protected function reportShippingStatusReport(array $f): array
    {
        $rows = TransportShipment::with(['customer', 'driver', 'branch'])
            ->whereBetween('ship_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->orderByRaw("FIELD(status, 'pending','dispatched','in_transit','delivered','failed','cancelled')")
            ->get()
            ->map(fn ($s) => [
                'shipment' => $s->shipment_no,
                'date' => $s->ship_date?->format('Y-m-d'),
                'customer' => $s->customer?->name,
                'driver' => $s->driver?->name ?? '—',
                'status' => $s->status,
                'expected' => $s->expected_date?->format('Y-m-d') ?? '—',
                'delivered' => $s->delivered_at?->format('Y-m-d') ?? '—',
            ]);

        return $this->table([
            $this->col('shipment', 'Shipment', 'الشحنة'),
            $this->col('date', 'Ship Date', 'تاريخ الشحن'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('driver', 'Driver', 'السائق'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('expected', 'Expected', 'المتوقع'),
            $this->col('delivered', 'Delivered', 'التسليم'),
        ], $rows);
    }

    protected function reportAttendanceTime(array $f): array
    {
        $rows = AttendanceRecord::with(['employee.department', 'employee.branch'])
            ->whereBetween('attendance_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $b)))
            ->orderBy('attendance_date')
            ->orderBy('employee_id')
            ->get()
            ->map(fn ($r) => [
                'date' => $r->attendance_date->format('Y-m-d'),
                'employee' => localized($r->employee),
                'department' => localized($r->employee?->department),
                'status' => $r->status,
                'check_in' => $r->check_in,
                'check_out' => $r->check_out,
            ]);

        return $this->table([
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('employee', 'Employee', 'الموظف'),
            $this->col('department', 'Department', 'القسم'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('check_in', 'Check In', 'الدخول'),
            $this->col('check_out', 'Check Out', 'الخروج'),
        ], $rows);
    }

    protected function reportNoLocationParts(array $f): array
    {
        $rows = Part::where('is_active', true)
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where('part_number', 'like', "%{$s}%"))
            ->where(function ($q) {
                $q->whereNull('aisle')->orWhere('aisle', '')
                    ->where(function ($q2) {
                        $q2->whereNull('rack')->orWhere('rack', '');
                    })
                    ->where(function ($q2) {
                        $q2->whereNull('bin')->orWhere('bin', '');
                    });
            })
            ->orderBy('part_number')
            ->get()
            ->map(fn ($p) => [
                'part' => $p->part_number,
                'description' => localized($p, 'description_en', 'description_ar'),
                'aisle' => $p->aisle ?: '—',
                'rack' => $p->rack ?: '—',
                'bin' => $p->bin ?: '—',
            ]);

        return $this->table([
            $this->col('part', 'Part No', 'رقم القطعة'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('aisle', 'Aisle', 'الممر'),
            $this->col('rack', 'Rack', 'الرف'),
            $this->col('bin', 'Bin', 'الصندوق'),
        ], $rows);
    }

    protected function reportDeliverySummary(array $f): array
    {
        $rows = \App\Models\DeliveryNote::with(['customer', 'branch'])
            ->whereBetween('delivery_date', [$f['from'], $f['to']])
            ->when($f['branch_id'] ?? null, fn ($q, $b) => $q->where('branch_id', $b))
            ->withCount('items')
            ->orderByDesc('delivery_date')
            ->get()
            ->map(fn ($n) => [
                'dn_no' => $n->dn_no,
                'date' => $n->delivery_date?->format('Y-m-d'),
                'customer' => localized($n->customer),
                'branch' => localized($n->branch),
                'items' => $n->items_count,
                'status' => $n->status,
                'driver' => $n->driver_name ?? '—',
            ]);

        return $this->table([
            $this->col('dn_no', 'DN No', 'رقم التسليم'),
            $this->col('date', 'Date', 'التاريخ'),
            $this->col('customer', 'Customer', 'العميل'),
            $this->col('branch', 'Branch', 'الفرع'),
            $this->col('items', 'Lines', 'البنود', 'right'),
            $this->col('status', 'Status', 'الحالة'),
            $this->col('driver', 'Driver', 'السائق'),
        ], $rows);
    }

    protected function reportLabelsReport(array $f): array
    {
        $rows = Part::where('is_active', true)
            ->when($f['search'] ?? null, fn ($q, $s) => $q->where(function ($q2) use ($s) {
                $q2->where('part_number', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%");
            }))
            ->orderBy('part_number')
            ->limit(500)
            ->get()
            ->map(fn ($p) => [
                'part' => $p->part_number,
                'barcode' => $p->barcode ?: $p->part_number,
                'description' => localized($p, 'description_en', 'description_ar'),
                'label_url' => route('documents.part.label', $p),
            ]);

        return $this->table([
            $this->col('part', 'Part No', 'رقم القطعة'),
            $this->col('barcode', 'Barcode', 'الباركود'),
            $this->col('description', 'Description', 'الوصف'),
            $this->col('label_url', 'Print', 'طباعة'),
        ], $rows);
    }

    public function filterOptions(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(['id', 'part_number', 'description_en']),
            'movement_types' => StockMovement::distinct()->pluck('movement_type')->filter()->values(),
            'job_statuses' => ['open', 'in_progress', 'completed', 'invoiced', 'cancelled'],
            'drivers' => TransportDriver::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'shipment_statuses' => TransportShipment::STATUSES,
        ];
    }
}
