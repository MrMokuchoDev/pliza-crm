<?php

namespace App\Providers;

use App\Infrastructure\Http\Livewire\Dashboard\DashboardIndex;
use App\Infrastructure\Http\Livewire\Deals\DealFormModal;
use App\Infrastructure\Http\Livewire\Deals\DealIndex;
use App\Infrastructure\Http\Livewire\Deals\DealKanban;
use App\Infrastructure\Http\Livewire\Deals\DealShow;
use App\Infrastructure\Http\Livewire\Leads\LeadFormModal;
use App\Infrastructure\Http\Livewire\Leads\LeadIndex;
use App\Infrastructure\Http\Livewire\Leads\LeadShow;
use App\Infrastructure\Http\Livewire\Maintenance\MaintenancePanel;
use App\Infrastructure\Http\Livewire\SalePhases\SalePhaseIndex;
use App\Infrastructure\Http\Livewire\Sites\SiteIndex;
use App\Infrastructure\Http\Livewire\Sites\SiteStatistics;
use App\Infrastructure\Http\Livewire\Roles\RolePermissions;
use App\Infrastructure\Http\Livewire\Updates\UpdatesPanel;
use App\Infrastructure\Http\Livewire\Users\UserIndex;
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
        Livewire::component('app.infrastructure.http.livewire.dashboard.dashboard-index', DashboardIndex::class);
        Livewire::component('app.infrastructure.http.livewire.sale-phases.sale-phase-index', SalePhaseIndex::class);
        Livewire::component('app.infrastructure.http.livewire.leads.lead-index', LeadIndex::class);
        Livewire::component('app.infrastructure.http.livewire.leads.lead-show', LeadShow::class);
        Livewire::component('lead-form-modal', LeadFormModal::class);
        Livewire::component('deal-form-modal', DealFormModal::class);
        Livewire::component('app.infrastructure.http.livewire.deals.deal-index', DealIndex::class);
        Livewire::component('app.infrastructure.http.livewire.deals.deal-show', DealShow::class);
        Livewire::component('app.infrastructure.http.livewire.deals.deal-kanban', DealKanban::class);
        Livewire::component('app.infrastructure.http.livewire.maintenance.maintenance-panel', MaintenancePanel::class);
        Livewire::component('app.infrastructure.http.livewire.sites.site-index', SiteIndex::class);
        Livewire::component('app.infrastructure.http.livewire.sites.site-statistics', SiteStatistics::class);
        Livewire::component('app.infrastructure.http.livewire.updates.updates-panel', UpdatesPanel::class);
        Livewire::component('app.infrastructure.http.livewire.users.user-index', UserIndex::class);
        Livewire::component('app.infrastructure.http.livewire.roles.role-permissions', RolePermissions::class);
    }
}
