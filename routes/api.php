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
});
