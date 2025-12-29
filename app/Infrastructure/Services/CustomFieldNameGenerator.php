<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\CustomField\Services\CustomFieldNameGeneratorInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Domain\CustomField\ValueObjects\FieldName;
use App\Infrastructure\Persistence\Eloquent\CustomFieldCounterModel;
use Illuminate\Support\Facades\DB;

final class CustomFieldNameGenerator implements CustomFieldNameGeneratorInterface
{
    public function generateNext(EntityType $entityType): FieldName
    {
        return DB::transaction(function () use ($entityType) {
            // Obtener y bloquear el contador para evitar race conditions
            $counter = CustomFieldCounterModel::where('entity_type', $entityType->value)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // Si no existe, crear el contador
                $counter = CustomFieldCounterModel::create([
                    'entity_type' => $entityType->value,
                    'counter' => 0,
                ]);
            }

            // Incrementar el contador
            $nextNumber = $counter->counter + 1;
            $counter->update(['counter' => $nextNumber]);

            // Generar el nombre del campo
            return FieldName::generate($entityType, $nextNumber);
        });
    }

    public function getCurrentCounter(EntityType $entityType): int
    {
        $counter = CustomFieldCounterModel::where('entity_type', $entityType->value)
            ->first();

        return $counter ? $counter->counter : 0;
    }
}
