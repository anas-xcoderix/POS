<?php

namespace App\Services\Legacy;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Franchise;
use App\Models\JobCard;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\Origin;
use App\Models\Part;
use App\Models\PaymentReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\Legacy\Concerns\ImportsLegacyDocuments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyImportService
{
    use ImportsLegacyDocuments;
    protected LegacySchemaReader $schema;

    protected LegacyRowMapper $row;

    protected LegacyIdMapService $maps;

    /** @var callable|null */
    protected $logger = null;

    protected ?Branch $defaultBranch = null;

    protected ?Location $defaultLocation = null;

    protected ?Brand $defaultBrand = null;

    protected ?Origin $defaultOrigin = null;

    protected ?Franchise $defaultFranchise = null;

    public function __construct(
        LegacySchemaReader $schema,
        LegacyRowMapper $row,
        LegacyIdMapService $maps,
    ) {
        $this->schema = $schema;
        $this->row = $row;
        $this->maps = $maps;
    }

    public function setConnection(string $connection): self
    {
        $this->schema->setConnection($connection);

        return $this;
    }

    public function onLog(callable $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    protected function log(string $message): void
    {
        if ($this->logger) {
            ($this->logger)($message);
        }
    }

    public function inspect(): array
    {
        return $this->schema->inspect();
    }

    public function listTables(): \Illuminate\Support\Collection
    {
        return $this->schema->tables();
    }

    /** @param  array<string, mixed>  $options */
    public function run(string $phase, array $options = []): LegacyImportStats
    {
        $stats = new LegacyImportStats;
        $phases = config('legacy_import.phases', []);
        $entities = $phase === 'all'
            ? collect($phases)->flatten()->unique()->values()->all()
            : ($phases[$phase] ?? []);

        if (! $entities) {
            throw new \InvalidArgumentException("Unknown import phase: {$phase}");
        }

        $this->maps->warm();
        $this->ensureDefaults();

        foreach ($entities as $entity) {
            if ($entity === 'stock_balances') {
                $stats->merge($this->importStockBalances($options));
                continue;
            }
            $stats->merge($this->importEntity($entity, $options));
        }

        return $stats;
    }

    /** @param  array<string, mixed>  $options */
    public function importEntity(string $entity, array $options = []): LegacyImportStats
    {
        $def = config("legacy_import.entities.{$entity}");
        if (! $def) {
            throw new \InvalidArgumentException("Unknown entity: {$entity}");
        }

        $table = $this->schema->resolveTable($def['legacy_tables'] ?? []);
        if (! $table) {
            $this->log("Skip {$entity}: legacy table not found (".implode(', ', $def['legacy_tables'] ?? []).')');

            return new LegacyImportStats;
        }

        if (! empty($def['detail_table'])) {
            return $this->importHeaderDetail($entity, $def, $table, $options);
        }

        return match ($entity) {
            'parts' => $this->importParts($def, $table, $options),
            default => $this->importSimpleRows($entity, $def, $table, $options),
        };
    }

    protected function ensureDefaults(): void
    {
        $d = config('legacy_import.defaults');

        $this->defaultBranch = Branch::firstOrCreate(
            ['code' => $d['branch_code']],
            ['name' => $d['branch_name'], 'is_active' => true, 'is_head_office' => true]
        );

        $this->defaultLocation = Location::firstOrCreate(
            ['branch_id' => $this->defaultBranch->id, 'code' => $d['location_code']],
            ['name' => $d['location_name'], 'is_active' => true]
        );

        $this->defaultBrand = Brand::firstOrCreate(
            ['code' => $d['brand_code']],
            ['name' => $d['brand_name'], 'is_active' => true]
        );

        $this->defaultOrigin = Origin::firstOrCreate(
            ['code' => $d['origin_code']],
            ['name' => $d['origin_name'], 'is_active' => true]
        );

        $this->defaultFranchise = Franchise::firstOrCreate(
            ['code' => $d['franchise_code']],
            ['name' => $d['franchise_name'], 'is_active' => true]
        );

        Unit::firstOrCreate(
            ['code' => $d['unit_code']],
            ['name' => $d['unit_name']]
        );
    }

    /** @param  array<string, mixed>  $def */
    protected function importSimpleRows(string $entity, array $def, string $table, array $options): LegacyImportStats
    {
        $stats = new LegacyImportStats;
        $limit = (int) ($options['limit'] ?? 0);
        $skipExisting = (bool) ($options['skip_existing'] ?? true);
        $count = 0;

        foreach ($this->schema->chunk($table, (int) ($options['chunk'] ?? 500), $limit) as $row) {
            $stats->processed++;
            $legacyKey = $this->legacyKey($row, $def['legacy_key'] ?? ['ID']);
            if (! $legacyKey) {
                $stats->skipped++;

                continue;
            }
            if ($skipExisting && $this->maps->find($entity, $legacyKey)) {
                $stats->skipped++;

                continue;
            }

            try {
                $localId = $this->upsertSimpleEntity($entity, $def, $row, $legacyKey);
                if ($localId) {
                    $this->maps->remember($entity, $legacyKey, $localId, $table);
                    $stats->imported++;
                } else {
                    $stats->skipped++;
                }
            } catch (\Throwable $e) {
                $stats->failed++;
                $this->log("{$entity} [{$legacyKey}]: ".$e->getMessage());
            }
            $count++;
        }

        $this->log("{$entity}: imported {$stats->imported}, skipped {$stats->skipped}, failed {$stats->failed}");

        return $stats;
    }

    /** @param  array<string, mixed>  $def */
    protected function upsertSimpleEntity(string $entity, array $def, object $row, string $legacyKey): ?int
    {
        $map = $def['map'] ?? [];

        return match ($entity) {
            'branches' => $this->upsertBranch($row, $map, $legacyKey),
            'departments' => Department::updateOrCreate(
                ['code' => $this->row->str($row, $map['code'] ?? ['Code']) ?: $legacyKey],
                [
                    'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $legacyKey,
                    'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                    'is_active' => true,
                ]
            )->id,
            'locations' => $this->upsertLocation($row, $map, $legacyKey),
            'brands' => Brand::updateOrCreate(
                ['code' => $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 20, '')],
                [
                    'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $legacyKey,
                    'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                    'is_active' => true,
                ]
            )->id,
            'origins' => Origin::updateOrCreate(
                ['code' => $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 20, '')],
                ['name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $legacyKey, 'is_active' => true]
            )->id,
            'franchises' => Franchise::updateOrCreate(
                ['code' => $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 20, '')],
                ['name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $legacyKey, 'is_active' => true]
            )->id,
            'units' => Unit::updateOrCreate(
                ['code' => $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 10, '')],
                ['name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $legacyKey]
            )->id,
            'customers' => $this->upsertCustomer($row, $map, $legacyKey),
            'vendors' => $this->upsertVendor($row, $map, $legacyKey),
            'accounts' => $this->upsertAccount($row, $map, $legacyKey),
            'vehicles' => $this->upsertVehicle($row, $map, $legacyKey),
            'employees' => $this->upsertEmployee($row, $map, $legacyKey),
            'job_cards' => $this->upsertJobCard($row, $map, $legacyKey),
            'stock_movements' => $this->insertStockMovement($row, $map, $legacyKey),
            default => null,
        };
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertBranch(object $row, array $map, string $legacyKey): int
    {
        $code = $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 20, '');
        $branch = Branch::updateOrCreate(
            ['code' => $code],
            [
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $code,
                'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                'phone' => $this->row->str($row, $map['phone'] ?? []),
                'address' => $this->row->str($row, $map['address'] ?? []),
                'is_active' => true,
                'is_head_office' => Branch::count() === 0,
            ]
        );

        return $branch->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertLocation(object $row, array $map, string $legacyKey): int
    {
        $branchId = $this->resolveBranchId($this->row->str($row, $map['branch'] ?? [])) ?? $this->defaultBranch->id;
        $code = $this->row->str($row, $map['code'] ?? ['Code']) ?: Str::limit($legacyKey, 30, '');

        return Location::updateOrCreate(
            ['branch_id' => $branchId, 'code' => $code],
            [
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $code,
                'aisle' => $this->row->str($row, $map['aisle'] ?? []),
                'rack' => $this->row->str($row, $map['rack'] ?? []),
                'bin' => $this->row->str($row, $map['bin'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }

    /** @param  array<string, mixed>  $def */
    protected function importParts(array $def, string $table, array $options): LegacyImportStats
    {
        $stats = new LegacyImportStats;
        $limit = (int) ($options['limit'] ?? 0);
        $skipExisting = (bool) ($options['skip_existing'] ?? true);
        $map = $def['map'] ?? [];

        foreach ($this->schema->chunk($table, (int) ($options['chunk'] ?? 500), $limit) as $row) {
            $stats->processed++;
            $legacyKey = $this->legacyKey($row, $def['legacy_key'] ?? ['ItemCode']);
            if (! $legacyKey) {
                $stats->skipped++;

                continue;
            }
            if ($skipExisting && $this->maps->find('parts', $legacyKey)) {
                $stats->skipped++;

                continue;
            }

            try {
                $brandLegacy = $this->row->str($row, $map['brand'] ?? []);
                $brandId = $brandLegacy ? ($this->maps->find('brands', $brandLegacy) ?? $this->defaultBrand->id) : $this->defaultBrand->id;

                $part = Part::updateOrCreate(
                    ['part_number' => $legacyKey],
                    [
                        'oem_no' => $this->row->str($row, $map['oem_no'] ?? []),
                        'manufacturer_part_no' => $this->row->str($row, $map['manufacturer_part_no'] ?? []),
                        'barcode' => $this->row->str($row, $map['barcode'] ?? []),
                        'brand_id' => $brandId,
                        'origin_id' => $this->resolveOptionalMap('origins', $this->row->str($row, $map['origin'] ?? [])) ?? $this->defaultOrigin->id,
                        'franchise_id' => $this->resolveOptionalMap('franchises', $this->row->str($row, $map['franchise'] ?? [])) ?? $this->defaultFranchise->id,
                        'description_en' => $this->row->str($row, $map['description_en'] ?? ['Description']) ?: $legacyKey,
                        'description_ar' => $this->row->str($row, $map['description_ar'] ?? []),
                        'list_price' => $this->row->decimal($row, $map['list_price'] ?? []),
                        'price_2' => $this->row->decimal($row, $map['price_2'] ?? []),
                        'price_3' => $this->row->decimal($row, $map['price_3'] ?? []),
                        'cost_price' => $this->row->decimal($row, $map['cost_price'] ?? []),
                        'min_stock' => $this->row->decimal($row, $map['min_stock'] ?? []),
                        'max_stock' => $this->row->decimal($row, $map['max_stock'] ?? []),
                        'hs_code' => $this->row->str($row, $map['hs_code'] ?? []),
                        'weight' => $this->row->decimal($row, $map['weight'] ?? []),
                        'is_active' => $this->row->bool($row, $map['active'] ?? [], true),
                    ]
                );

                $this->maps->remember('parts', $legacyKey, $part->id, $table);
                $stats->imported++;
            } catch (\Throwable $e) {
                $stats->failed++;
                $this->log("parts [{$legacyKey}]: ".$e->getMessage());
            }
        }

        $this->log("parts: imported {$stats->imported}, skipped {$stats->skipped}, failed {$stats->failed}");

        return $stats;
    }

    /** @param  array<string, mixed>  $options */
    protected function importStockBalances(array $options): LegacyImportStats
    {
        $stats = new LegacyImportStats;
        $def = config('legacy_import.entities.parts');
        $table = $this->schema->resolveTable($def['legacy_tables'] ?? []);
        if (! $table) {
            $this->log('Skip stock_balances: Item table not found');

            return $stats;
        }

        $limit = (int) ($options['limit'] ?? 0);
        $qtyColumns = $def['stock_qty_columns'] ?? [];
        $singleQty = $def['stock_single_qty'] ?? ['Qty'];
        $branchKeys = $def['stock_branch'] ?? ['StoreID'];

        foreach ($this->schema->chunk($table, (int) ($options['chunk'] ?? 500), $limit) as $row) {
            $stats->processed++;
            $partKey = $this->legacyKey($row, $def['legacy_key'] ?? ['ItemCode']);
            $partId = $partKey ? $this->maps->find('parts', $partKey) : null;
            if (! $partId) {
                $stats->skipped++;

                continue;
            }

            $branchId = $this->resolveBranchId($this->row->str($row, $branchKeys)) ?? $this->defaultBranch->id;
            $importedAny = false;

            foreach ($qtyColumns as $slot) {
                $qty = $this->row->decimal($row, $slot['qty'] ?? []);
                if ($qty <= 0) {
                    continue;
                }
                $locKey = $this->row->str($row, $slot['location'] ?? []);
                $locationId = $locKey
                    ? ($this->maps->find('locations', $locKey) ?? $this->defaultLocation->id)
                    : $this->defaultLocation->id;

                StockBalance::updateOrCreate(
                    ['branch_id' => $branchId, 'location_id' => $locationId, 'part_id' => $partId],
                    [
                        'quantity' => $qty,
                        'avg_cost' => $this->row->decimal($row, $def['map']['cost_price'] ?? ['AveCost']),
                    ]
                );
                $importedAny = true;
            }

            if (! $importedAny) {
                $qty = $this->row->decimal($row, $singleQty);
                if ($qty > 0) {
                    StockBalance::updateOrCreate(
                        ['branch_id' => $branchId, 'location_id' => $this->defaultLocation->id, 'part_id' => $partId],
                        [
                            'quantity' => $qty,
                            'avg_cost' => $this->row->decimal($row, $def['map']['cost_price'] ?? ['AveCost']),
                        ]
                    );
                    $importedAny = true;
                }
            }

            $importedAny ? $stats->imported++ : $stats->skipped++;
        }

        $this->log("stock_balances: imported {$stats->imported}, skipped {$stats->skipped}");

        return $stats;
    }

    /** @param  array<string, mixed>  $def */
    protected function importHeaderDetail(string $entity, array $def, string $headerTable, array $options): LegacyImportStats
    {
        $stats = new LegacyImportStats;
        $detailTable = $this->schema->resolveTable($def['detail_table'] ?? []);
        if (! $detailTable) {
            $this->log("Skip {$entity}: detail table not found");

            return $stats;
        }

        $limit = (int) ($options['limit'] ?? 0);
        $skipExisting = (bool) ($options['skip_existing'] ?? true);
        $filter = $def['filter'] ?? null;
        $detailFk = $def['detail_fk'] ?? ['HeaderID'];
        $headerMap = $def['map'] ?? [];
        $detailMap = $def['detail_map'] ?? [];
        $count = 0;

        $detailsByHeader = $this->groupDetails($detailTable, $detailFk);

        foreach ($this->schema->chunk($headerTable, (int) ($options['chunk'] ?? 200), $limit) as $header) {
            $stats->processed++;
            if ($filter && ! $this->passesFilter($header, $filter)) {
                $stats->skipped++;

                continue;
            }

            $legacyKey = $this->legacyKey($header, $def['legacy_key'] ?? ['ID']);
            if (! $legacyKey) {
                $stats->skipped++;

                continue;
            }
            if ($skipExisting && $this->maps->find($entity, $legacyKey)) {
                $stats->skipped++;

                continue;
            }

            $detailRows = $this->matchDetails($header, $detailsByHeader, $detailFk, $legacyKey);

            try {
                DB::transaction(function () use ($entity, $header, $detailRows, $legacyKey, $headerTable, $headerMap, $detailMap, &$stats) {
                    $localId = $this->upsertHeaderDetail($entity, $header, $detailRows, $headerMap, $detailMap, $legacyKey);
                    if ($localId) {
                        $this->maps->remember($entity, $legacyKey, $localId, $headerTable);
                        $stats->imported++;
                    } else {
                        $stats->skipped++;
                    }
                });
            } catch (\Throwable $e) {
                $stats->failed++;
                $this->log("{$entity} [{$legacyKey}]: ".$e->getMessage());
            }
            $count++;
        }

        $this->log("{$entity}: imported {$stats->imported}, skipped {$stats->skipped}, failed {$stats->failed}");

        return $stats;
    }

    /** @param  array<string, array<int, string>>  $filter */
    protected function passesFilter(object $row, array $filter): bool
    {
        foreach ($filter as $field => $allowed) {
            $val = $this->row->val($row, [$field]);
            if ($val === null || $val === '') {
                continue;
            }
            $val = strtoupper((string) $val);
            $allowedUpper = array_map('strtoupper', $allowed);
            if (! in_array($val, $allowedUpper, true)) {
                return false;
            }
        }

        return true;
    }

    /** @param  array<int, string>  $detailFk */
    protected function groupDetails(string $detailTable, array $detailFk): array
    {
        $grouped = [];
        foreach ($this->schema->chunk($detailTable, 2000) as $detail) {
            $key = $this->legacyKey($detail, $detailFk) ?? 'unknown';
            $grouped[$key][] = $detail;
        }

        return $grouped;
    }

    /** @param  array<int, string>  $detailFk */
    protected function matchDetails(object $header, array $grouped, array $detailFk, string $headerKey): array
    {
        foreach ($detailFk as $fk) {
            $k = $this->legacyKey($header, [$fk]) ?? $headerKey;
            if (isset($grouped[$k])) {
                return $grouped[$k];
            }
        }

        return $grouped[$headerKey] ?? [];
    }

    /** @param  array<int, object>  $detailRows */
    protected function upsertHeaderDetail(
        string $entity,
        object $header,
        array $detailRows,
        array $headerMap,
        array $detailMap,
        string $legacyKey,
    ): ?int {
        return match ($entity) {
            'quotations' => $this->upsertQuotation($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'sales_invoices' => $this->upsertSalesInvoice($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'sale_returns' => $this->upsertSaleReturn($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'purchase_orders' => $this->upsertPurchaseOrder($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'purchase_invoices' => $this->upsertPurchaseInvoice($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'journal_entries' => $this->upsertJournalEntry($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            'payment_receipts' => $this->upsertPaymentReceipt($header, $headerMap, $legacyKey),
            'stock_transfers' => $this->upsertStockTransfer($header, $detailRows, $headerMap, $detailMap, $legacyKey),
            default => null,
        };
    }

    // --- Entity upsert helpers continue in ImportsLegacyDocuments trait ---

    /** @param  array<string, mixed>  $map */
    protected function upsertCustomer(object $row, array $map, string $legacyKey): int
    {
        $code = $this->row->str($row, $map['code'] ?? ['Code']) ?: $legacyKey;

        return Customer::updateOrCreate(
            ['code' => $code],
            [
                'branch_id' => $this->resolveBranchId($this->row->str($row, $map['branch'] ?? [])),
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $code,
                'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                'contact_person' => $this->row->str($row, $map['contact_person'] ?? []),
                'phone' => $this->row->str($row, $map['phone'] ?? []),
                'mobile' => $this->row->str($row, $map['mobile'] ?? []),
                'email' => $this->row->str($row, $map['email'] ?? []),
                'address' => $this->row->str($row, $map['address'] ?? []),
                'city' => $this->row->str($row, $map['city'] ?? []),
                'country' => $this->row->str($row, $map['country'] ?? []),
                'vat_no' => $this->row->str($row, $map['vat_no'] ?? []),
                'customer_type' => strtolower($this->row->str($row, $map['customer_type'] ?? [], 'retail') ?? 'retail'),
                'credit_limit' => $this->row->decimal($row, $map['credit_limit'] ?? []),
                'balance' => $this->row->decimal($row, $map['balance'] ?? []),
                'payment_terms_days' => $this->row->int($row, $map['payment_terms'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertVendor(object $row, array $map, string $legacyKey): int
    {
        $code = $this->row->str($row, $map['code'] ?? ['Code']) ?: $legacyKey;

        return Vendor::updateOrCreate(
            ['code' => $code],
            [
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $code,
                'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                'phone' => $this->row->str($row, $map['phone'] ?? []),
                'email' => $this->row->str($row, $map['email'] ?? []),
                'address' => $this->row->str($row, $map['address'] ?? []),
                'vat_no' => $this->row->str($row, $map['vat_no'] ?? []),
                'balance' => $this->row->decimal($row, $map['balance'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }

    /** @param  array<string, mixed>  $map */
    protected function upsertAccount(object $row, array $map, string $legacyKey): int
    {
        $code = $this->row->str($row, $map['account_code'] ?? ['Code']) ?: $legacyKey;
        $type = strtolower($this->row->str($row, $map['account_type'] ?? ['Type'], 'asset') ?? 'asset');

        return Account::updateOrCreate(
            ['account_code' => $code],
            [
                'name' => $this->row->str($row, $map['name'] ?? ['Name']) ?: $code,
                'name_ar' => $this->row->str($row, $map['name_ar'] ?? []),
                'account_type' => in_array($type, ['asset', 'liability', 'equity', 'revenue', 'expense']) ? $type : 'asset',
                'opening_balance' => $this->row->decimal($row, $map['opening_balance'] ?? []),
                'current_balance' => $this->row->decimal($row, $map['current_balance'] ?? []),
                'is_active' => true,
            ]
        )->id;
    }

    protected function legacyKey(object $row, array|string $keys): ?string
    {
        return $this->row->legacyKey($row, $keys);
    }

    protected function resolveBranchId(?string $legacyKey): ?int
    {
        if (! $legacyKey) {
            return $this->defaultBranch?->id;
        }

        return $this->maps->find('branches', $legacyKey) ?? $this->defaultBranch?->id;
    }

    protected function resolveOptionalMap(string $entity, ?string $legacyKey): ?int
    {
        return $legacyKey ? $this->maps->find($entity, $legacyKey) : null;
    }

    protected function resolvePartId(?string $partKey): ?int
    {
        return $partKey ? $this->maps->find('parts', $partKey) : null;
    }

    protected function resolveCustomerId(?string $custKey): ?int
    {
        if (! $custKey) {
            return Customer::value('id');
        }

        return $this->maps->find('customers', $custKey) ?? Customer::where('code', $custKey)->value('id');
    }

    protected function resolveVendorId(?string $vendKey): ?int
    {
        if (! $vendKey) {
            return Vendor::value('id');
        }

        return $this->maps->find('vendors', $vendKey) ?? Vendor::where('code', $vendKey)->value('id');
    }

    protected function resolveSalesInvoiceId(?string $key): ?int
    {
        if (! $key) {
            return null;
        }

        return $this->maps->find('sales_invoices', $key)
            ?? SalesInvoice::where('invoice_no', $key)->value('id');
    }

    protected function mapDocumentStatus(object $row, array $statusKeys): string
    {
        if ($this->row->bool($row, $statusKeys)) {
            return 'posted';
        }
        $status = strtolower($this->row->str($row, $statusKeys, 'draft') ?? 'draft');

        return in_array($status, ['posted', 'draft', 'cancelled', 'open', 'closed', 'received'], true) ? $status : 'posted';
    }
}
