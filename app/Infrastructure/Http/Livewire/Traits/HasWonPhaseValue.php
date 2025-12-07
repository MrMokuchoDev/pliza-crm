<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Traits;

use App\Application\Deal\Services\DealService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Deal\Services\DealPhaseService;

/**
 * Trait para manejar el modal de valor al cerrar un negocio como ganado.
 *
 * Proporciona la lógica común para solicitar el valor de un negocio
 * cuando se mueve a una fase de "Cerrado Ganado" y no tiene valor asignado.
 *
 * Uso:
 * 1. Usar el trait en el componente
 * 2. Llamar a handlePhaseChange() en lugar de cambiar fase directamente
 * 3. Incluir el modal de valor en la vista Blade
 */
trait HasWonPhaseValue
{
    public bool $showValueModal = false;

    public ?string $pendingWonDealId = null;

    public ?string $pendingWonPhaseId = null;

    public ?string $dealValue = null;

    /**
     * Manejar el cambio de fase, detectando si requiere valor.
     *
     * @return array{success: bool, message: string}|null Null si se abrió el modal de valor
     */
    protected function handlePhaseChange(string $dealId, string $phaseId): ?array
    {
        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);

        $deal = $dealService->findWithRelations($dealId);
        $newPhase = $phaseService->find($phaseId);

        if (! $deal || ! $newPhase) {
            return ['success' => false, 'message' => 'Negocio o fase no encontrado'];
        }

        $service = new DealPhaseService();
        $validation = $service->canChangePhase($deal, $newPhase);

        if (! $validation['can_change']) {
            if ($validation['reason'] === DealPhaseService::RESULT_NO_CHANGE) {
                return null;
            }

            if ($validation['reason'] === DealPhaseService::RESULT_REQUIRES_VALUE) {
                $this->openWonValueModal($dealId, $phaseId, $deal->value);

                return null;
            }

            return ['success' => false, 'message' => $service->getErrorMessage($validation['reason'])];
        }

        $result = $service->applyPhaseChange($deal, $newPhase);

        return ['success' => true, 'message' => $result['message']];
    }

    /**
     * Abrir el modal para ingresar el valor del negocio ganado.
     */
    protected function openWonValueModal(string $dealId, string $phaseId, ?float $currentValue = null): void
    {
        $this->pendingWonDealId = $dealId;
        $this->pendingWonPhaseId = $phaseId;
        $this->dealValue = $currentValue ? (string) $currentValue : null;
        $this->showValueModal = true;
    }

    /**
     * Confirmar el cierre del negocio con el valor ingresado.
     */
    public function confirmWonWithValue(): void
    {
        $validationRules = DealPhaseService::getWonValueValidationRules();
        $this->validate($validationRules['rules'], $validationRules['messages']);

        $dealService = app(DealService::class);
        $phaseService = app(SalePhaseService::class);

        $deal = $dealService->find($this->pendingWonDealId);
        $newPhase = $phaseService->find($this->pendingWonPhaseId);

        if (! $deal || ! $newPhase) {
            $this->cancelWonPhase();

            return;
        }

        $service = new DealPhaseService();
        $result = $service->changePhaseWithValue($deal, $newPhase, (float) $this->dealValue);

        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);

        $this->cancelWonPhase();
    }

    /**
     * Cancelar el proceso de cierre con valor.
     */
    public function cancelWonPhase(): void
    {
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }
}
