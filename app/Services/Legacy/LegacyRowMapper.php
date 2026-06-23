<?php

namespace App\Services\Legacy;

use Carbon\Carbon;

class LegacyRowMapper
{
    /** @param  array<string, mixed>|object  $row */
    public function val(array|object $row, array|string $keys, mixed $default = null): mixed
    {
        $keys = (array) $keys;
        $data = (array) $row;

        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }

        // Case-insensitive fallback for SQL Server column casing differences
        $lower = array_change_key_case($data, CASE_LOWER);
        foreach ($keys as $key) {
            $lk = strtolower($key);
            if (array_key_exists($lk, $lower) && $lower[$lk] !== null && $lower[$lk] !== '') {
                return $lower[$lk];
            }
        }

        return $default;
    }

    /** @param  array<string, mixed>|object  $row */
    public function str(array|object $row, array|string $keys, ?string $default = null): ?string
    {
        $v = $this->val($row, $keys, $default);

        return $v === null ? null : trim((string) $v);
    }

    /** @param  array<string, mixed>|object  $row */
    public function decimal(array|object $row, array|string $keys, float $default = 0): float
    {
        $v = $this->val($row, $keys);

        return $v === null || $v === '' ? $default : (float) $v;
    }

    /** @param  array<string, mixed>|object  $row */
    public function int(array|object $row, array|string $keys, int $default = 0): int
    {
        $v = $this->val($row, $keys);

        return $v === null || $v === '' ? $default : (int) $v;
    }

    /** @param  array<string, mixed>|object  $row */
    public function bool(array|object $row, array|string $keys, bool $default = false): bool
    {
        $v = $this->val($row, $keys);
        if ($v === null || $v === '') {
            return $default;
        }

        if (is_bool($v)) {
            return $v;
        }

        return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'y', 'posted', 'post'], true);
    }

    /** @param  array<string, mixed>|object  $row */
    public function date(array|object $row, array|string $keys): ?string
    {
        $v = $this->val($row, $keys);
        if ($v === null || $v === '') {
            return null;
        }

        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param  array<string, mixed>|object  $row */
    public function legacyKey(array|object $row, array|string $keys): ?string
    {
        $v = $this->str($row, $keys);

        return $v ?: null;
    }
}
