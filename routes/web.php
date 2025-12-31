<?php

use App\Http\Controllers\ProfileController;
use App\Infrastructure\Http\Livewire\CustomField\CustomFieldIndex;
use App\Infrastructure\Http\Livewire\Dashboard\DashboardIndex;
use App\Infrastructure\Http\Livewire\Deals\DealIndex;
use App\Infrastructure\Http\Livewire\Deals\DealKanban;
use App\Infrastructure\Http\Livewire\Deals\DealShow;
use App\Infrastructure\Http\Livewire\Leads\LeadIndex;
use App\Infrastructure\Http\Livewire\Leads\LeadShow;
use App\Infrastructure\Http\Livewire\Maintenance\MaintenancePanel;
use App\Infrastructure\Http\Livewire\SalePhases\SalePhaseIndex;
use App\Infrastructure\Http\Livewire\Sites\SiteIndex;
use App\Infrastructure\Http\Livewire\Sites\SiteStatistics;
use App\Infrastructure\Http\Livewire\Roles\RolePermissions;
use App\Infrastructure\Http\Livewire\Updates\UpdatesPanel;
use App\Infrastructure\Http\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;

// Ruta pública - redirige a login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Rutas protegidas del panel administrativo
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Leads
    Route::get('/leads', LeadIndex::class)->name('leads.index');
    Route::get('/leads/{id}', LeadShow::class)->name('leads.show');

    // Deals
    Route::get('/deals', DealIndex::class)->name('deals.index');
    Route::get('/deals/{id}', DealShow::class)->name('deals.show');
    Route::get('/pipeline', DealKanban::class)->name('deals.kanban');

    // =========================================
    // Rutas de Configuración - Basadas en permisos
    // El middleware soporta OR con | para múltiples permisos
    // =========================================

    // Sale Phases - requiere permiso phases.manage o cualquier permiso granular de sale_phases
    Route::get('/sale-phases', SalePhaseIndex::class)
        ->middleware('permission:phases.manage|sale_phases.view|sale_phases.create|sale_phases.update|sale_phases.delete')
        ->name('sale-phases.index');

    // Sites - requiere permiso sites.manage o cualquier permiso granular de sites
    Route::get('/sites', SiteIndex::class)
        ->middleware('permission:sites.manage|sites.view|sites.create|sites.update|sites.delete')
        ->name('sites.index');

    Route::get('/sites/{siteId}/statistics', SiteStatistics::class)
        ->middleware('permission:sites.manage|sites.view')
        ->name('sites.statistics');

    // Users - requiere permiso users.view o cualquier permiso de usuarios
    Route::get('/users', UserIndex::class)
        ->middleware('permission:users.view|users.create|users.update|users.delete')
        ->name('users.index');

    // Roles & Permissions - requiere permiso users.assign_role
    Route::get('/roles', RolePermissions::class)
        ->middleware('permission:users.assign_role')
        ->name('roles.index');

    // Custom Fields - requiere permiso custom_fields.view o system.maintenance
    Route::get('/custom-fields', CustomFieldIndex::class)
        ->middleware('permission:system.maintenance|custom_fields.view|custom_fields.create|custom_fields.update|custom_fields.delete')
        ->name('custom-fields.index');

    // Maintenance Panel - requiere permiso system.maintenance
    Route::get('/admin/maintenance', MaintenancePanel::class)
        ->middleware('permission:system.maintenance')
        ->name('maintenance');

    // Updates Panel - requiere permiso system.updates
    Route::get('/admin/updates', UpdatesPanel::class)
        ->middleware('permission:system.updates')
        ->name('updates');

    // Profile (de Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
