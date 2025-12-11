<?php

use App\Http\Controllers\ProfileController;
use App\Infrastructure\Http\Livewire\Dashboard\DashboardIndex;
use App\Infrastructure\Http\Livewire\Deals\DealIndex;
use App\Infrastructure\Http\Livewire\Deals\DealKanban;
use App\Infrastructure\Http\Livewire\Deals\DealShow;
use App\Infrastructure\Http\Livewire\Leads\LeadIndex;
use App\Infrastructure\Http\Livewire\Leads\LeadShow;
use App\Infrastructure\Http\Livewire\Maintenance\MaintenancePanel;
use App\Infrastructure\Http\Livewire\SalePhases\SalePhaseIndex;
use App\Infrastructure\Http\Livewire\Sites\SiteIndex;
use App\Infrastructure\Http\Livewire\Updates\UpdatesPanel;
use Illuminate\Support\Facades\Route;

// Ruta pública - redirige a login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Rutas protegidas del panel administrativo
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Contactos (Leads)
    Route::get('/contactos', LeadIndex::class)->name('leads.index');
    Route::get('/contactos/{id}', LeadShow::class)->name('leads.show');

    // Negocios (Deals)
    Route::get('/negocios', DealIndex::class)->name('deals.index');
    Route::get('/negocios/{id}', DealShow::class)->name('deals.show');
    Route::get('/pipeline', DealKanban::class)->name('deals.kanban');

    // Sale Phases
    Route::get('/sale-phases', SalePhaseIndex::class)->name('sale-phases.index');

    // Sites
    Route::get('/sites', SiteIndex::class)->name('sites.index');

    // Maintenance Panel
    Route::get('/admin/maintenance', MaintenancePanel::class)->name('maintenance');

    // Updates Panel
    Route::get('/admin/updates', UpdatesPanel::class)->name('updates');

    // Profile (de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
