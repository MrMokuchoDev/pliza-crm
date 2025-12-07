<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\FindLeadByContactQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para buscar un Lead por email o teléfono.
 */
class FindLeadByContactHandler
{
    public function handle(FindLeadByContactQuery $query): ?LeadModel
    {
        // Buscar por email primero
        if ($query->email) {
            $builder = LeadModel::where('email', $query->email);
            if ($query->lockForUpdate) {
                $builder->lockForUpdate();
            }
            $lead = $builder->first();
            if ($lead) {
                return $lead;
            }
        }

        // Buscar por teléfono normalizado
        if ($query->phone) {
            $normalizedPhone = $this->normalizePhone($query->phone);
            if (strlen($normalizedPhone) >= 7) {
                $lastDigits = substr($normalizedPhone, -7);
                $builder = LeadModel::whereNotNull('phone')
                    ->where('phone', 'like', '%'.$lastDigits.'%');

                if ($query->lockForUpdate) {
                    $builder->lockForUpdate();
                }

                return $builder->get()
                    ->first(fn ($lead) => $this->normalizePhone($lead->phone) === $normalizedPhone);
            }
        }

        return null;
    }

    /**
     * Normalizar teléfono removiendo espacios, guiones, paréntesis y +
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s\-\(\)\+]/', '', $phone);
    }
}
