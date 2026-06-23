<?php

namespace App\Services\Legacy;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LegacySchemaReader
{
    public function __construct(
        protected string $connection = 'legacy_sqlsrv',
    ) {}

    public function setConnection(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function connection(): Connection
    {
        return DB::connection($this->connection);
    }

    /** @return Collection<int, string> */
    public function tables(): Collection
    {
        $driver = $this->connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $this->connection()->select(
                "SELECT name AS TABLE_NAME FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            );
        } else {
            $rows = $this->connection()->select(
                "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
            );
        }

        return collect($rows)->pluck('TABLE_NAME');
    }

    /** @return Collection<int, string> */
    public function columns(string $table): Collection
    {
        $driver = $this->connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $this->connection()->select('PRAGMA table_info('.str_replace("'", '', $table).')');

            return collect($rows)->pluck('name');
        }

        $rows = $this->connection()->select(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$table]
        );

        return collect($rows)->pluck('COLUMN_NAME');
    }

    public function resolveTable(array $candidates, ?Collection $tables = null): ?string
    {
        $tables ??= $this->tables();
        $lower = $tables->mapWithKeys(fn ($t) => [strtolower($t) => $t]);

        foreach ($candidates as $candidate) {
            if ($tables->contains($candidate)) {
                return $candidate;
            }
            $hit = $lower[strtolower($candidate)] ?? null;
            if ($hit) {
                return $hit;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function inspect(?Collection $tables = null): array
    {
        $tables ??= $this->tables();
        $config = config('legacy_import.entities', []);
        $out = [];

        foreach ($config as $entity => $def) {
            $resolved = $this->resolveTable($def['legacy_tables'] ?? [], $tables);
            $out[$entity] = [
                'target' => $def['target'] ?? null,
                'legacy_table' => $resolved,
                'legacy_tables' => $def['legacy_tables'] ?? [],
                'columns' => $resolved ? $this->columns($resolved)->all() : [],
            ];
        }

        return $out;
    }

    /** @return \Generator<int, object> */
    public function chunk(string $table, int $chunk = 500, int $limit = 0): \Generator
    {
        $query = $this->connection()->table($table)->orderByRaw('1');
        $offset = 0;

        while (true) {
            $rows = (clone $query)->offset($offset)->limit($chunk)->get();
            if ($rows->isEmpty()) {
                break;
            }
            foreach ($rows as $row) {
                yield $row;
                if ($limit > 0 && $offset >= $limit) {
                    return;
                }
            }
            $offset += $rows->count();
            if ($limit > 0 && $offset >= $limit) {
                return;
            }
            if ($rows->count() < $chunk) {
                break;
            }
        }
    }

    public function count(string $table): int
    {
        return (int) $this->connection()->table($table)->count();
    }
}
