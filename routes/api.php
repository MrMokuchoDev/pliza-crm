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
});
