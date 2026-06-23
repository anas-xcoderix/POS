<?php

namespace App\Services\Legacy;

use App\Models\LegacyImportMap;
use Illuminate\Support\Facades\Cache;

class LegacyIdMapService
{
    /** @var array<string, array<string, int>> */
    protected array $memory = [];

    public function remember(string $entityType, string $legacyKey, int $localId, ?string $legacyTable = null): void
    {
        LegacyImportMap::updateOrCreate(
            ['entity_type' => $entityType, 'legacy_key' => $legacyKey],
            ['local_id' => $localId, 'legacy_table' => $legacyTable]
        );
        $this->memory[$entityType][$legacyKey] = $localId;
    }

    public function find(string $entityType, ?string $legacyKey): ?int
    {
        if (! $legacyKey) {
            return null;
        }

        if (isset($this->memory[$entityType][$legacyKey])) {
            return $this->memory[$entityType][$legacyKey];
        }

        $id = LegacyImportMap::where('entity_type', $entityType)
            ->where('legacy_key', $legacyKey)
            ->value('local_id');

        if ($id) {
            $this->memory[$entityType][$legacyKey] = (int) $id;
        }

        return $id ? (int) $id : null;
    }

    public function clear(?string $entityType = null): void
    {
        if ($entityType) {
            LegacyImportMap::where('entity_type', $entityType)->delete();
            unset($this->memory[$entityType]);
        } else {
            LegacyImportMap::query()->delete();
            $this->memory = [];
        }
        Cache::forget('legacy_import_maps_warmed');
    }

    public function warm(array $entityTypes = []): void
    {
        $query = LegacyImportMap::query();
        if ($entityTypes) {
            $query->whereIn('entity_type', $entityTypes);
        }
        foreach ($query->get() as $map) {
            $this->memory[$map->entity_type][$map->legacy_key] = (int) $map->local_id;
        }
    }
}
