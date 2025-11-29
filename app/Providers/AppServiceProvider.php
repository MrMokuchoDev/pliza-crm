<?php

namespace App\Providers;

use App\Infrastructure\Http\Livewire\Leads\LeadFormModal;
use App\Infrastructure\Http\Livewire\Leads\LeadIndex;
use App\Infrastructure\Http\Livewire\Leads\LeadShow;
use App\Infrastructure\Http\Livewire\Maintenance\MaintenancePanel;
use App\Infrastructure\Http\Livewire\SalePhases\SalePhaseIndex;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar componentes Livewire desde Infrastructure
        Livewire::component('app.infrastructure.http.livewire.sale-phases.sale-phase-index', SalePhaseIndex::class);
        Livewire::component('app.infrastructure.http.livewire.leads.lead-index', LeadIndex::class);
        Livewire::component('app.infrastructure.http.livewire.leads.lead-show', LeadShow::class);
        Livewire::component('lead-form-modal', LeadFormModal::class);
        Livewire::component('app.infrastructure.http.livewire.maintenance.maintenance-panel', MaintenancePanel::class);
    }
}
