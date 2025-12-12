<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

/**
 * Enum que representa los roles disponibles en el sistema.
 */
enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SALES = 'sales';

    /**
     * Obtiene el nombre para mostrar del rol.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::MANAGER => 'Gerente',
            self::SALES => 'Vendedor',
        };
    }

    /**
     * Obtiene la descripción del rol.
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Acceso total al sistema, gestión de usuarios y configuración',
            self::MANAGER => 'Gestión de leads y negocios, asignación a vendedores, reportes',
            self::SALES => 'Gestión de leads y negocios asignados',
        };
    }

    /**
     * Obtiene el nivel jerárquico del rol (mayor = más permisos).
     */
    public function level(): int
    {
        return match ($this) {
            self::ADMIN => 100,
            self::MANAGER => 50,
            self::SALES => 10,
        };
    }

    /**
     * Verifica si este rol puede gestionar otro rol.
     */
    public function canManage(Role $otherRole): bool
    {
        return $this->level() > $otherRole->level();
    }

    /**
     * Obtiene los permisos predeterminados para este rol.
     *
     * @return Permission[]
     */
    public function defaultPermissions(): array
    {
        return Permission::forRole($this);
    }
}
