<?php

declare(strict_types=1);

namespace App\Domain\SalePhase\Repositories;

use App\Domain\SalePhase\Entities\SalePhase;

interface SalePhaseRepositoryInterface
{
    public function findById(string $id): ?SalePhase;

    public function findDefault(): ?SalePhase;

    /** @return SalePhase[] */
    public function findAll(): array;

    /** @return SalePhase[] */
    public function findAllOpen(): array;

    /** @return SalePhase[] */
    public function findAllClosed(): array;

    public function save(SalePhase $salePhase): void;

    public function delete(string $id): void;

    public function updateOrder(array $orderedIds): void;

    public function getNextOrder(): int;

    public function countActive(): int;
}
