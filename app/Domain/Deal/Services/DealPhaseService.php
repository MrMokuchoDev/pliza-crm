<?php

declare(strict_types=1);

namespace App\Domain\Deal\Services;

use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;

/**
 * Servicio de dominio para gestionar cambios de fase en negocios.
 * Centraliza la lógica de transición de fases para evitar duplicación.
 */
class DealPhaseService
{
    /**
     * Resultado de la operación de cambio de fase.
     */
    public const RESULT_SUCCESS = 'success';
    public const RESULT_REQUIRES_VALUE = 'requires_value';
    public const RESULT_LEAD_HAS_OPEN_DEAL = 'lead_has_open_deal';
    public const RESULT_NO_CHANGE = 'no_change';
    public const RESULT_NOT_FOUND = 'not_found';

    /**
     * Verificar si un negocio puede cambiar a una fase específica.
     *
     * @return array{can_change: bool, reason: string, requires_value?: bool}
     */
    public function canChangePhase(DealModel $deal, SalePhaseModel $newPhase): array
    {
        // Sin cambio
        if ($deal->sale_phase_id === $newPhase->id) {
            return [
                'can_change' => false,
                'reason' => self::RESULT_NO_CHANGE,
            ];
        }

        $currentPhase = $deal->salePhase;

        // Si el negocio está cerrado y se quiere mover a una fase abierta,
        // verificar que el contacto no tenga otro negocio abierto
        if ($currentPhase?->is_closed && ! $newPhase->is_closed) {
            if ($deal->lead && $deal->lead->hasOpenDeal($deal->id)) {
                return [
                    'can_change' => false,
                    'reason' => self::RESULT_LEAD_HAS_OPEN_DEAL,
                ];
            }
        }

        // Si se quiere cerrar como GANADO, se necesita valor
        if ($newPhase->is_closed && $newPhase->is_won && empty($deal->value)) {
            return [
                'can_change' => false,
                'reason' => self::RESULT_REQUIRES_VALUE,
                'requires_value' => true,
            ];
        }

        return [
            'can_change' => true,
            'reason' => self::RESULT_SUCCESS,
        ];
    }

    /**
     * Aplicar el cambio de fase a un negocio.
     *
     * @return array{success: bool, message: string}
     */
    public function applyPhaseChange(DealModel $deal, SalePhaseModel $newPhase): array
    {
        $updateData = [
            'sale_phase_id' => $newPhase->id,
            'updated_at' => now(),
        ];

        // Si se mueve a fase cerrada, establecer fecha de cierre
        if ($newPhase->is_closed && ! $deal->close_date) {
            $updateData['close_date'] = now();
        }

        // Si se mueve a fase abierta, limpiar fecha de cierre
        if (! $newPhase->is_closed) {
            $updateData['close_date'] = null;
        }

        $deal->update($updateData);

        $message = $this->getPhaseChangeMessage($newPhase);

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    /**
     * Cambiar la fase de un negocio con todas las validaciones.
     *
     * @return array{success: bool, reason: string, message: string, requires_value?: bool}
     */
    public function changePhase(DealModel $deal, SalePhaseModel $newPhase): array
    {
        $validation = $this->canChangePhase($deal, $newPhase);

        if (! $validation['can_change']) {
            return [
                'success' => false,
                'reason' => $validation['reason'],
                'message' => $this->getErrorMessage($validation['reason']),
                'requires_value' => $validation['requires_value'] ?? false,
            ];
        }

        $result = $this->applyPhaseChange($deal, $newPhase);

        return [
            'success' => true,
            'reason' => self::RESULT_SUCCESS,
            'message' => $result['message'],
        ];
    }

    /**
     * Cambiar fase con valor (para cerrar como ganado).
     *
     * @return array{success: bool, reason: string, message: string}
     */
    public function changePhaseWithValue(DealModel $deal, SalePhaseModel $newPhase, float $value): array
    {
        // Actualizar valor primero usando magic setter (value es custom field: cf_deal_2)
        $deal->value = $value;
        $deal->save();

        return $this->changePhase($deal, $newPhase);
    }

    /**
     * Obtener mensaje de éxito según la fase.
     */
    public function getPhaseChangeMessage(SalePhaseModel $phase): string
    {
        if ($phase->is_closed) {
            return $phase->is_won
                ? 'Negocio marcado como ganado'
                : 'Negocio marcado como perdido';
        }

        return "Negocio movido a {$phase->name}";
    }

    /**
     * Obtener mensaje de error según la razón.
     */
    public function getErrorMessage(string $reason): string
    {
        return match ($reason) {
            self::RESULT_LEAD_HAS_OPEN_DEAL => 'Este contacto ya tiene un negocio abierto. Cierra o elimina el otro negocio antes de reabrir este.',
            self::RESULT_REQUIRES_VALUE => 'El valor del negocio es obligatorio para cerrarlo como ganado.',
            self::RESULT_NO_CHANGE => 'El negocio ya está en esta fase.',
            self::RESULT_NOT_FOUND => 'Negocio o fase no encontrados.',
            default => 'Error al cambiar la fase del negocio.',
        };
    }

    /**
     * Verificar si una fase es de tipo "cerrado ganado" y requiere valor.
     */
    public static function isWonPhase(SalePhaseModel $phase): bool
    {
        return $phase->is_closed && $phase->is_won;
    }

    /**
     * Validar si se puede cerrar como ganado con el valor proporcionado.
     *
     * @return array{valid: bool, error?: string}
     */
    public static function validateValueForWonPhase(SalePhaseModel $phase, mixed $value): array
    {
        if (! self::isWonPhase($phase)) {
            return ['valid' => true];
        }

        if (empty($value) || ! is_numeric($value)) {
            return [
                'valid' => false,
                'error' => 'El valor del negocio es obligatorio para cerrarlo como ganado.',
            ];
        }

        if ((float) $value < 0) {
            return [
                'valid' => false,
                'error' => 'El valor del negocio no puede ser negativo.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validar valor para el modal de cierre como ganado.
     * Retorna array con reglas y mensajes para usar con Livewire validate().
     *
     * @return array{rules: array, messages: array}
     */
    public static function getWonValueValidationRules(): array
    {
        return [
            'rules' => [
                'dealValue' => 'required|numeric|min:0',
            ],
            'messages' => [
                'dealValue.required' => 'El valor del negocio es obligatorio para cerrarlo como ganado.',
                'dealValue.numeric' => 'El valor debe ser un número válido.',
                'dealValue.min' => 'El valor no puede ser negativo.',
            ],
        ];
    }
}
