<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Traits;

/**
 * Trait para manejar confirmación de eliminación en componentes Livewire.
 *
 * Proporciona propiedades y métodos comunes para mostrar un modal de confirmación
 * antes de eliminar un registro.
 *
 * Uso:
 * 1. Usar el trait en el componente
 * 2. Implementar el método performDelete(string $id): bool
 * 3. Opcionalmente sobrescribir getDeleteSuccessMessage() y getDeleteErrorMessage()
 */
trait HasDeleteConfirmation
{
    public bool $showDeleteModal = false;

    public ?string $deletingId = null;

    /**
     * Abrir el modal de confirmación de eliminación.
     */
    public function openDeleteModal(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Cerrar el modal de confirmación de eliminación.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * Ejecutar la eliminación después de confirmar.
     */
    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $result = $this->performDelete($this->deletingId);

        if ($result) {
            $this->dispatch('notify', type: 'success', message: $this->getDeleteSuccessMessage());
        } else {
            $this->dispatch('notify', type: 'error', message: $this->getDeleteErrorMessage());
        }

        $this->closeDeleteModal();
    }

    /**
     * Ejecutar la eliminación real.
     * Debe ser implementado por el componente que use este trait.
     */
    abstract protected function performDelete(string $id): bool;

    /**
     * Mensaje de éxito al eliminar.
     * Puede ser sobrescrito por el componente.
     */
    protected function getDeleteSuccessMessage(): string
    {
        return 'Registro eliminado correctamente';
    }

    /**
     * Mensaje de error al eliminar.
     * Puede ser sobrescrito por el componente.
     */
    protected function getDeleteErrorMessage(): string
    {
        return 'Error al eliminar el registro';
    }
}
