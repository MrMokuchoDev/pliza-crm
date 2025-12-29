<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use App\Domain\CustomField\ValueObjects\FieldName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\UuidInterface;

final class CustomFieldOptionsTableManager implements CustomFieldOptionsTableManagerInterface
{
    public function createTable(FieldName $fieldName): void
    {
        $tableName = $fieldName->getOptionsTableName();

        if (Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table already exists: {$tableName}");
        }

        Schema::create($tableName, function ($table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('value');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index('order');
        });
    }

    public function dropTable(FieldName $fieldName): void
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::dropIfExists($tableName);
    }

    public function addOption(FieldName $fieldName, UuidInterface $id, string $label, string $value, int $order): void
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        DB::table($tableName)->insert([
            'id' => $id->toString(),
            'label' => $label,
            'value' => $value,
            'order' => $order,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updateOption(FieldName $fieldName, UuidInterface $id, string $label, string $value, int $order): void
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        DB::table($tableName)
            ->where('id', $id->toString())
            ->update([
                'label' => $label,
                'value' => $value,
                'order' => $order,
                'updated_at' => now(),
            ]);
    }

    public function deleteOption(FieldName $fieldName, UuidInterface $id): void
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        DB::table($tableName)
            ->where('id', $id->toString())
            ->delete();
    }

    public function getOptions(FieldName $fieldName): array
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        return DB::table($tableName)
            ->orderBy('order')
            ->get()
            ->map(fn($row) => [
                'id' => $row->id,
                'label' => $row->label,
                'value' => $row->value,
                'order' => $row->order,
            ])
            ->toArray();
    }

    public function getOption(FieldName $fieldName, UuidInterface $id): ?array
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        $row = DB::table($tableName)
            ->where('id', $id->toString())
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'id' => $row->id,
            'label' => $row->label,
            'value' => $row->value,
            'order' => $row->order,
        ];
    }

    public function getNextOrder(FieldName $fieldName): int
    {
        $tableName = $fieldName->getOptionsTableName();

        if (!Schema::hasTable($tableName)) {
            throw new \RuntimeException("Options table does not exist: {$tableName}");
        }

        $maxOrder = DB::table($tableName)->max('order');

        return $maxOrder !== null ? $maxOrder + 1 : 0;
    }

    public function tableExists(FieldName $fieldName): bool
    {
        $tableName = $fieldName->getOptionsTableName();
        return Schema::hasTable($tableName);
    }
}
