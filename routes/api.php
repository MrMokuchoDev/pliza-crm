<?php

use App\Infrastructure\Http\Controllers\Api\V1\LeadCaptureController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Lead capture - público, sin autenticación
    // Rate limit: 30 requests por minuto por IP para prevenir spam
    Route::post('/leads/capture', [LeadCaptureController::class, 'capture'])
        ->middleware('throttle:30,1')
        ->name('api.leads.capture');

    // Verificar si el sitio está activo - para widgets
    Route::get('/sites/{siteId}/status', function (string $siteId) {
        $site = \App\Infrastructure\Persistence\Eloquent\SiteModel::find($siteId);

        return response()->json([
            'active' => $site && $site->is_active,
        ]);
    })->middleware('throttle:120,1')->name('api.sites.status');

    // Obtener custom fields para widgets - público, sin autenticación
    Route::get('/custom-fields/widget', function () {
        $fields = \Illuminate\Support\Facades\DB::table('custom_fields')
            ->where('entity_type', 'lead')
            ->where('is_active', 1)
            ->orderBy('order', 'asc')
            ->get(['id', 'name', 'label', 'type', 'is_required', 'validation_rules', 'default_value']);

        $customFields = [];

        foreach ($fields as $field) {
            $options = null;

            // Si el campo requiere opciones, consultar tabla dinámica
            if (in_array($field->type, ['select', 'radio', 'multiselect'])) {
                $optionsTableName = $field->name . '_options';
                if (\Illuminate\Support\Facades\Schema::hasTable($optionsTableName)) {
                    $optionsData = \Illuminate\Support\Facades\DB::table($optionsTableName)
                        ->orderBy('order')
                        ->get(['label', 'value'])
                        ->toArray();

                    if (!empty($optionsData)) {
                        $options = array_map(fn($opt) => [
                            'label' => $opt->label,
                            'value' => $opt->value
                        ], $optionsData);
                    }
                }
            }

            $customFields[] = [
                'name' => $field->name,
                'label' => $field->label,
                'type' => $field->type,
                'required' => (bool) $field->is_required,
                'validation' => $field->validation_rules,
                'options' => $options,
                'default_value' => $field->default_value,
            ];
        }

        return response()->json($customFields);
    })->middleware('throttle:120,1')->name('api.custom-fields.widget');
});
