<?php

use App\Http\Controllers\ProfileController;
use App\Infrastructure\Http\Livewire\Leads\LeadIndex;
use App\Infrastructure\Http\Livewire\Leads\LeadShow;
use App\Infrastructure\Http\Livewire\Maintenance\MaintenancePanel;
use App\Infrastructure\Http\Livewire\SalePhases\SalePhaseIndex;
use Illuminate\Support\Facades\Route;

// Ruta pública - redirige a login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Rutas protegidas del panel administrativo
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return redirect()->route('leads.index');
    })->name('dashboard');

    // Leads
    Route::get('/leads', LeadIndex::class)->name('leads.index');
    Route::get('/leads/{id}', LeadShow::class)->name('leads.show');

    // Kanban
    Route::get('/kanban', function () {
        return view('pages.kanban');
    })->name('kanban');

    // Sale Phases
    Route::get('/sale-phases', SalePhaseIndex::class)->name('sale-phases.index');

    // Sites
    Route::get('/sites', function () {
        return view('pages.sites.index');
    })->name('sites.index');

    // Maintenance Panel
    Route::get('/admin/maintenance', MaintenancePanel::class)->name('maintenance');

    // Profile (de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
