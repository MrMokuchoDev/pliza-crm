<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Sites;

use App\Application\Site\Services\SiteService;
use Carbon\Carbon;
use Livewire\Component;

class SiteStatistics extends Component
{
    public string $siteId;

    public string $dateFrom;

    public string $dateTo;

    public string $periodPreset = '30d';

    public function mount(string $siteId): void
    {
        $this->siteId = $siteId;
        $this->setDateRange('30d');
    }

    public function setDateRange(string $preset): void
    {
        $this->periodPreset = $preset;

        $now = Carbon::now();

        switch ($preset) {
            case '7d':
                $this->dateFrom = $now->copy()->subDays(7)->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
            case '30d':
                $this->dateFrom = $now->copy()->subDays(30)->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
            case '90d':
                $this->dateFrom = $now->copy()->subDays(90)->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
            case '12m':
                $this->dateFrom = $now->copy()->subMonths(12)->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
            case 'all':
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }
    }

    public function updatedDateFrom(): void
    {
        $this->periodPreset = 'custom';
    }

    public function updatedDateTo(): void
    {
        $this->periodPreset = 'custom';
    }

    public function getStatisticsProperty(): array
    {
        $service = app(SiteService::class);

        return $service->getStatistics(
            $this->siteId,
            $this->dateFrom ?: null,
            $this->dateTo ?: null
        );
    }

    public function getSourceTypeLabel(string $sourceType): string
    {
        return match ($sourceType) {
            'whatsapp_button' => 'WhatsApp',
            'phone_button' => 'Llamada',
            'contact_form' => 'Formulario',
            'manual' => 'Manual',
            default => ucfirst($sourceType),
        };
    }

    public function getSourceTypeColor(string $sourceType): string
    {
        return match ($sourceType) {
            'whatsapp_button' => 'bg-green-500',
            'phone_button' => 'bg-blue-500',
            'contact_form' => 'bg-purple-500',
            'manual' => 'bg-gray-500',
            default => 'bg-gray-400',
        };
    }

    public function render()
    {
        return view('livewire.sites.statistics')
            ->layout('components.layouts.app', ['title' => 'Estadisticas del Sitio']);
    }
}
