<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, ?string $default = null): ?string
    {
        $settings = $this->all();

        return $settings[$key] ?? $default ?? config("erp.default_settings.{$key}");
    }

    public function getFloat(string $key, float $default = 0): float
    {
        return (float) ($this->get($key, (string) $default) ?? $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default ? '1' : '0');

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public function set(string $key, ?string $value, string $group = 'general'): void
    {
        SystemSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget('erp_settings');
    }

    public function setMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value === null ? null : (string) $value, $group);
        }
    }

    public function all(): array
    {
        return Cache::remember('erp_settings', 3600, function () {
            $db = SystemSetting::pluck('value', 'key')->toArray();

            return array_merge(config('erp.default_settings', []), $db);
        });
    }

    public function seedDefaults(): void
    {
        foreach (config('erp.default_settings', []) as $key => $value) {
            SystemSetting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
        }

        Cache::forget('erp_settings');
    }
}
