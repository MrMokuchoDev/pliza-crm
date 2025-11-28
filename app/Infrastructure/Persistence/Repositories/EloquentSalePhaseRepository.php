<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\SalePhase\Entities\SalePhase;
use App\Domain\SalePhase\Repositories\SalePhaseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

class EloquentSalePhaseRepository implements SalePhaseRepositoryInterface
{
    public function findById(string $id): ?SalePhase
    {
        $model = SalePhaseModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findDefault(): ?SalePhase
    {
        $model = SalePhaseModel::where('is_default', true)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return SalePhaseModel::orderBy('order')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findAllOpen(): array
    {
        return SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findAllClosed(): array
    {
        return SalePhaseModel::where('is_closed', true)
            ->orderBy('order')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function save(SalePhase $salePhase): void
    {
        SalePhaseModel::updateOrCreate(
            ['id' => $salePhase->id],
            [
                'name' => $salePhase->name,
                'order' => $salePhase->order,
                'color' => $salePhase->color,
                'is_closed' => $salePhase->isClosed,
                'is_won' => $salePhase->isWon,
                'is_default' => $salePhase->isDefault,
                'created_at' => $salePhase->createdAt,
            ]
        );
    }

    public function delete(string $id): void
    {
        SalePhaseModel::destroy($id);
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            SalePhaseModel::where('id', $id)->update(['order' => $order + 1]);
        }
    }

    public function getNextOrder(): int
    {
        return (int) SalePhaseModel::max('order') + 1;
    }

    public function countActive(): int
    {
        return SalePhaseModel::where('is_closed', false)->count();
    }

    private function toDomain(SalePhaseModel $model): SalePhase
    {
        return new SalePhase(
            id: $model->id,
            name: $model->name,
            order: $model->order,
            color: $model->color,
            isClosed: $model->is_closed,
            isWon: $model->is_won,
            isDefault: $model->is_default,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
        );
    }
}
