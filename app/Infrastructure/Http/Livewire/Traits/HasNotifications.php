<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Traits;

/**
 * Trait para enviar notificaciones toast desde componentes Livewire.
 *
 * Proporciona métodos helper para enviar notificaciones de diferentes tipos
 * sin tener que repetir la sintaxis de dispatch en cada componente.
 */
trait HasNotifications
{
    /**
     * Enviar notificación de éxito.
     */
    protected function notifySuccess(string $message): void
    {
        $this->dispatch('notify', type: 'success', message: $message);
    }

    /**
     * Enviar notificación de error.
     */
    protected function notifyError(string $message): void
    {
        $this->dispatch('notify', type: 'error', message: $message);
    }

    /**
     * Enviar notificación de información.
     */
    protected function notifyInfo(string $message): void
    {
        $this->dispatch('notify', type: 'info', message: $message);
    }

    /**
     * Enviar notificación de advertencia.
     */
    protected function notifyWarning(string $message): void
    {
        $this->dispatch('notify', type: 'warning', message: $message);
    }
}
