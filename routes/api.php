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
    Route::post('/leads/capture', [LeadCaptureController::class, 'capture'])
        ->name('api.leads.capture');
});
