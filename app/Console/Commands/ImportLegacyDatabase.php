<?php

namespace App\Console\Commands;

use App\Models\LegacyImportRun;
use App\Services\Legacy\LegacyIdMapService;
use App\Services\Legacy\LegacyImportService;
use App\Services\Legacy\LegacySchemaReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyDatabase extends Command
{
    protected $signature = 'iaapco:import-legacy
                            {--connection=legacy_sqlsrv : Legacy DB connection (legacy_sqlsrv or legacy_sqlite)}
                            {--dry-run : List legacy tables only}
                            {--inspect : Show entity mapping and column match}
                            {--phase=all : Import phase: organization|masters|inventory|sales|purchase|finance|workshop|hr|all}
                            {--entity= : Import single entity key from config/legacy_import.php}
                            {--limit=0 : Max rows per table (0 = unlimited)}
                            {--chunk=500 : Chunk size when reading legacy tables}
                            {--skip-existing : Skip rows already in legacy_import_maps}
                            {--fresh-maps : Clear ID maps before import}
                            {--force : Skip confirmation on full import}';

    protected $description = 'Import data from legacy IAAPCO SQL Server (InventoryHas) into Laravel ERP';

    public function handle(LegacyImportService $import, LegacySchemaReader $schema, LegacyIdMapService $maps): int
    {
        $connection = $this->option('connection');

        if (! config("database.connections.{$connection}")) {
            $this->error("Connection [{$connection}] is not configured.");

            return self::FAILURE;
        }

        $import->setConnection($connection);
        $schema->setConnection($connection);
        $import->onLog(fn (string $msg) => $this->line($msg));

        try {
            $tables = $import->listTables();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to legacy database: '.$e->getMessage());
            $this->newLine();
            $this->warn('Configure SQL Server in .env:');
            $this->line('  LEGACY_DB_HOST=JS-SERVER');
            $this->line('  LEGACY_DB_DATABASE=InventoryHas');
            $this->line('  LEGACY_DB_USERNAME=sa');
            $this->line('  LEGACY_DB_PASSWORD=your_password');
            $this->newLine();
            $this->info('For local testing without SQL Server:');
            $this->line('  php artisan iaapco:legacy-mock-setup');
            $this->line('  php artisan iaapco:import-legacy --connection=legacy_sqlite --phase=all');

            return self::FAILURE;
        }

        $this->info("Connected to [{$connection}] — {$tables->count()} tables found.");

        if ($this->option('dry-run')) {
            foreach ($tables as $name) {
                $this->line('  - '.$name);
            }

            return self::SUCCESS;
        }

        if ($this->option('inspect')) {
            return $this->renderInspect($import->inspect());
        }

        $phase = $this->option('phase') ?? 'all';
        $entity = $this->option('entity');

        if ($this->option('fresh-maps')) {
            $maps->clear();
            $this->warn('Cleared legacy ID maps.');
        }

        if ($phase === 'all' && ! $entity && ! $this->option('force')) {
            if (! $this->confirm('Import ALL phases from legacy database into MySQL? Existing mapped records will be updated.', false)) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }
        }

        $options = [
            'limit' => (int) $this->option('limit'),
            'chunk' => (int) $this->option('chunk'),
            'skip_existing' => (bool) $this->option('skip-existing'),
        ];

        $this->info('Starting legacy import...');

        $run = LegacyImportRun::create([
            'connection' => $connection,
            'phase' => $entity ?: $phase,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            if ($entity) {
                $stats = $import->importEntity($entity, $options);
            } else {
                $stats = $import->run($phase, $options);
            }
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $this->error('Import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $run->markFinished('completed', $stats);

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $stats->processed],
                ['Imported', $stats->imported],
                ['Skipped', $stats->skipped],
                ['Failed', $stats->failed],
            ]
        );

        if ($stats->failed > 0) {
            $this->warn('Some rows failed — re-run with --inspect and check column mappings in config/legacy_import.php');
        } else {
            $this->info('Legacy import completed.');
        }

        return self::SUCCESS;
    }

    protected function renderInspect(array $inspect): int
    {
        foreach ($inspect as $entity => $info) {
            $this->newLine();
            $this->info(strtoupper($entity).' → '.($info['target'] ?? '?'));
            if ($info['legacy_table']) {
                $this->line('  Legacy table: '.$info['legacy_table']);
                $this->line('  Columns: '.implode(', ', array_slice($info['columns'], 0, 12)).(count($info['columns']) > 12 ? '...' : ''));
            } else {
                $this->warn('  Legacy table NOT FOUND. Candidates: '.implode(', ', $info['legacy_tables']));
            }
        }

        return self::SUCCESS;
    }
}
