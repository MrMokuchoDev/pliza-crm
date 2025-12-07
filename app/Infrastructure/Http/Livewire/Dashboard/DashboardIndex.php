<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Dashboard;

use App\Application\Dashboard\Services\DashboardService;
use Livewire\Component;

class DashboardIndex extends Component
{
    public array $stats = [];

    public array $leadsBySource = [];

    public array $dealsByPhase = [];

    public array $conversionFunnel = [];

    public array $leadsTrend = [];

    public string $trendPeriod = 'daily';

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $service = app(DashboardService::class);

        $this->stats = $service->getStats()->toArray();
        $this->leadsBySource = $service->getLeadsBySource();
        $this->dealsByPhase = $service->getDealsByPhase();
        $this->conversionFunnel = $service->getConversionFunnel();
        $this->loadTrend();
    }

    public function loadTrend(): void
    {
        $service = app(DashboardService::class);
        $limit = match ($this->trendPeriod) {
            'weekly' => 12,
            'monthly' => 12,
            default => 30,
        };
        $this->leadsTrend = $service->getLeadsTrend($this->trendPeriod, $limit);
    }

    public function updatedTrendPeriod(): void
    {
        $this->loadTrend();
        $this->dispatch('trendsUpdated', trends: $this->leadsTrend);
    }

    public function render()
    {
        return view('livewire.dashboard.index')
            ->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
