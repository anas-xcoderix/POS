<?php

namespace App\Http\Controllers;

use App\Models\LegacyImportMap;
use App\Models\LegacyImportRun;
use App\Services\Legacy\LegacyIdMapService;
use App\Services\Legacy\LegacyImportService;
use App\Services\Legacy\LegacySchemaReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegacyImportController extends Controller
{
    public function index(LegacySchemaReader $schema): View
    {
        return view('settings.legacy-import', [
            'phases' => config('legacy_import.phases', []),
            'connectionStatus' => [
                'legacy_sqlsrv' => $this->probeConnection($schema, 'legacy_sqlsrv'),
                'legacy_sqlite' => $this->probeConnection($schema, 'legacy_sqlite'),
            ],
            'importRuns' => LegacyImportRun::query()->latest('started_at')->limit(15)->get(),
            'mapCount' => LegacyImportMap::count(),
            'mapByEntity' => LegacyImportMap::query()
                ->selectRaw('entity_type, count(*) as total')
                ->groupBy('entity_type')
                ->orderBy('entity_type')
                ->pluck('total', 'entity_type'),
        ]);
    }

    public function run(Request $request, LegacyImportService $import, LegacyIdMapService $maps): RedirectResponse
    {
        set_time_limit(0);

        $data = $request->validate([
            'connection' => 'required|in:legacy_sqlsrv,legacy_sqlite',
            'phase' => 'required|string|max:50',
            'fresh_maps' => 'boolean',
        ]);

        $connection = $data['connection'];
        $phase = $data['phase'];

        if (! config("database.connections.{$connection}")) {
            return back()->with('error', "Connection [{$connection}] is not configured.");
        }

        $import->setConnection($connection);

        try {
            $import->listTables();
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot connect to legacy database: '.$e->getMessage());
        }

        if ($request->boolean('fresh_maps')) {
            $maps->clear();
        }

        $run = LegacyImportRun::create([
            'connection' => $connection,
            'phase' => $phase,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $logLines = [];
        $import->onLog(function (string $msg) use (&$logLines) {
            $logLines[] = $msg;
        });

        try {
            $stats = $import->run($phase, [
                'limit' => 0,
                'chunk' => 500,
                'skip_existing' => false,
            ]);
            $run->markFinished('completed', $stats, implode("\n", array_slice($logLines, -50)));

            return back()->with('success', sprintf(
                'Legacy import completed: %d imported, %d skipped, %d failed.',
                $stats->imported,
                $stats->skipped,
                $stats->failed
            ));
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /** @return array{ok: bool, connection: string, tables?: int, message: string} */
    protected function probeConnection(LegacySchemaReader $schema, string $connection): array
    {
        if (! config("database.connections.{$connection}")) {
            return [
                'ok' => false,
                'connection' => $connection,
                'message' => 'Connection not configured in database.php / .env',
            ];
        }

        try {
            $schema->setConnection($connection);
            $count = $schema->tables()->count();

            return [
                'ok' => true,
                'connection' => $connection,
                'tables' => $count,
                'message' => "Connected — {$count} legacy tables found.",
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'connection' => $connection,
                'message' => $e->getMessage(),
            ];
        }
    }
}
