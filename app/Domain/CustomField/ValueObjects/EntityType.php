<?php

declare(strict_types=1);

namespace App\Domain\CustomField\ValueObjects;

use App\Domain\CustomField\Repositories\EntityTypeRepositoryInterface;

/**
 * Value Object para tipo de entidad
 * 100% escalable y DDD puro: usa Repository para validar
 */
final class EntityType
{
    private static ?EntityTypeRepositoryInterface $repository = null;

    private function __construct(
        public readonly string $value
    ) {
        $this->validate();
    }

    /**
     * Inyectar repository (se hace una sola vez desde el Service Provider)
     */
    public static function setRepository(EntityTypeRepositoryInterface $repository): void
    {
        self::$repository = $repository;
    }

    private function validate(): void
    {
        // Validación de formato básico
        if (!preg_match('/^[a-z]+$/', $this->value)) {
            throw new \InvalidArgumentException(
                "Entity type must be lowercase letters only: {$this->value}"
            );
        }

        if (strlen($this->value) < 2 || strlen($this->value) > 50) {
            throw new \InvalidArgumentException(
                "Entity type must be between 2 and 50 characters"
            );
        }

        // Validar que existe en el sistema usando el repository
        if (self::$repository && !self::$repository->exists($this->value)) {
            throw new \DomainException(
                "Entity type '{$this->value}' is not registered or not active in the system"
            );
        }
    }

    /**
     * Crear desde string (factory principal)
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Alias para compatibilidad
     */
    public static function from(string $value): self
    {
        return self::fromString($value);
    }

    /**
     * Obtener label amigable
     */
    public function getLabel(): string
    {
        if (!self::$repository) {
            return ucfirst($this->value);
        }

        $label = self::$repository->getLabel($this->value);
        return $label ?? ucfirst($this->value);
    }

    /**
     * Comparar con otro EntityType
     */
    public function equals(EntityType $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Obtener todos los tipos disponibles
     */
    public static function all(): array
    {
        if (!self::$repository) {
            return [];
        }

        return self::$repository->getAllAvailable();
    }

    /**
     * Convertir a string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
