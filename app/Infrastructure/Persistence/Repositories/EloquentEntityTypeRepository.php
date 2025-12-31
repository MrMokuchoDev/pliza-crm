<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\CustomField\Repositories\EntityTypeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class EloquentEntityTypeRepository implements EntityTypeRepositoryInterface
{
    private const CACHE_KEY = 'entity_types_available';
    private const CACHE_TTL = 3600; // 1 hora

    public function exists(string $entityType): bool
    {
        $availableTypes = $this->getAllAvailable();
        return in_array($entityType, $availableTypes, true);
    }

    public function getAllAvailable(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return DB::table('entity_types')
                ->where('is_active', true)
                ->where('allows_custom_fields', true)
                ->orderBy('order')
                ->pluck('name')
                ->toArray();
        });
    }

    public function getLabel(string $entityType): ?string
    {
        $cacheKey = "entity_type_label_{$entityType}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($entityType) {
            return DB::table('entity_types')
                ->where('name', $entityType)
                ->value('label');
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        // Limpiar también los cachés de labels
        $types = DB::table('entity_types')->pluck('name');
        foreach ($types as $type) {
            Cache::forget("entity_type_label_{$type}");
        }
    }
}
