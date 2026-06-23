<?php

namespace App\Services\Legacy;

class LegacyImportStats
{
    public int $processed = 0;

    public int $imported = 0;

    public int $skipped = 0;

    public int $failed = 0;

    /** @var array<string, int> */
    public array $byEntity = [];

    public function bump(string $entity, string $type = 'imported'): void
    {
        $this->processed++;
        $this->{$type}++;
        $this->byEntity[$entity] = ($this->byEntity[$entity] ?? 0) + 1;
    }

    public function merge(self $other): void
    {
        $this->processed += $other->processed;
        $this->imported += $other->imported;
        $this->skipped += $other->skipped;
        $this->failed += $other->failed;
        foreach ($other->byEntity as $entity => $count) {
            $this->byEntity[$entity] = ($this->byEntity[$entity] ?? 0) + $count;
        }
    }
}
