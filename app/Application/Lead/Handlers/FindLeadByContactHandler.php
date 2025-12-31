<?php

declare(strict_types=1);

namespace App\Application\Lead\Handlers;

use App\Application\Lead\Queries\FindLeadByContactQuery;
use App\Infrastructure\Persistence\Eloquent\LeadModel;

/**
 * Handler para buscar un Lead por email o teléfono usando custom fields.
 */
class FindLeadByContactHandler
{
    public function handle(FindLeadByContactQuery $query): ?LeadModel
    {
        // Buscar por email primero (cf_lead_2)
        if ($query->email) {
            $builder = LeadModel::whereCustomField('cf_lead_2', $query->email);

            if ($query->lockForUpdate) {
                $builder->lockForUpdate();
            }

            $lead = $builder->first();
            if ($lead) {
                return $lead;
            }
        }

        // Buscar por teléfono normalizado (cf_lead_3)
        if ($query->phone) {
            $normalizedPhone = $this->normalizePhone($query->phone);
            if (strlen($normalizedPhone) >= 7) {
                $lastDigits = substr($normalizedPhone, -7);

                $builder = LeadModel::whereCustomFieldLike('cf_lead_3', '%'.$lastDigits.'%');

                if ($query->lockForUpdate) {
                    $builder->lockForUpdate();
                }

                return $builder->get()
                    ->first(function ($lead) use ($normalizedPhone) {
                        $phoneValue = $lead->cf_lead_3 ?? '';
                        return $this->normalizePhone($phoneValue) === $normalizedPhone;
                    });
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
