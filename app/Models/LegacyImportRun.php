<?php

namespace App\Models;

use App\Services\Legacy\LegacyImportStats;
use Illuminate\Database\Eloquent\Model;

class LegacyImportRun extends Model
{
    protected $fillable = [
        'connection',
        'phase',
        'status',
        'rows_processed',
        'rows_imported',
        'rows_skipped',
        'message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function markFinished(string $status, LegacyImportStats $stats, ?string $message = null): void
    {
        $this->update([
            'status' => $status,
            'rows_processed' => $stats->processed,
            'rows_imported' => $stats->imported,
            'rows_skipped' => $stats->skipped,
            'message' => $message,
            'finished_at' => now(),
        ]);
    }
}
