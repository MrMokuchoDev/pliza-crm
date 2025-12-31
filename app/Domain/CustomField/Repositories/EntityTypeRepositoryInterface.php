<?php

declare(strict_types=1);

namespace App\Domain\CustomField\Repositories;

/**
 * Repository para tipos de entidad disponibles
 * Interface en Domain (puerto hexagonal)
 */
interface EntityTypeRepositoryInterface
{
    /**
     * Verificar si un tipo de entidad existe y está activo
     */
    public function exists(string $entityType): bool;

    /**
     * Obtener todos los tipos disponibles
     * @return array<string>
     */
    public function getAllAvailable(): array;

    /**
     * Obtener el label de un tipo de entidad
     */
    public function getLabel(string $entityType): ?string;
}
