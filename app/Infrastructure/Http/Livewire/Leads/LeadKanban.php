<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadKanban extends Component
{
    public string $search = '';

    #[On('dealSaved')]
    #[On('leadSaved')]
    public function refreshBoard(): void
    {
        // El board se refresca automáticamente
    }

    public function openCreateModal(?string $leadId = null): void
    {
        $this->dispatch('openDealModal', leadId: $leadId);
    }

    public function openEditModal(string $dealId): void
    {
        $this->dispatch('openDealModal', dealId: $dealId);
    }

    public function moveToPhase(string $dealId, string $phaseId): void
    {
        $deal = DealModel::find($dealId);
        $phase = SalePhaseModel::find($phaseId);

        if (! $deal || ! $phase) {
            return;
        }

        // No hacer nada si se suelta en la misma fase
        if ($deal->sale_phase_id === $phaseId) {
            return;
        }

        $updateData = [
            'sale_phase_id' => $phaseId,
            'updated_at' => now(),
        ];

        // Si la fase es cerrada, establecer fecha de cierre
        if ($phase->is_closed && ! $deal->close_date) {
            $updateData['close_date'] = now();
        }

        $deal->update($updateData);

        $message = $phase->is_closed
            ? ($phase->is_won ? 'Negocio marcado como ganado' : 'Negocio marcado como perdido')
            : "Negocio movido a {$phase->name}";

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function render()
    {
        // Fases abiertas (para columnas)
        $openPhases = SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get();

        // Fases cerradas (para zonas de drop)
        $closedPhases = SalePhaseModel::where('is_closed', true)
            ->orderBy('order')
            ->get();

        // Deals por fase (solo de fases abiertas)
        $dealsByPhase = [];
        foreach ($openPhases as $phase) {
            $query = DealModel::with('lead')
                ->where('sale_phase_id', $phase->id)
                ->when($this->search, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('name', 'like', "%{$this->search}%")
                            ->orWhereHas('lead', function ($lq) {
                                $lq->where('name', 'like', "%{$this->search}%")
                                    ->orWhere('email', 'like', "%{$this->search}%")
                                    ->orWhere('phone', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->orderByDesc('updated_at')
                ->get();

            $dealsByPhase[$phase->id] = $query;
        }

        // Total de negocios activos
        $totalDeals = DealModel::whereIn('sale_phase_id', $openPhases->pluck('id'))->count();

        // Valor total de negocios activos
        $totalValue = DealModel::whereIn('sale_phase_id', $openPhases->pluck('id'))->sum('value');

        return view('livewire.leads.kanban', [
            'openPhases' => $openPhases,
            'closedPhases' => $closedPhases,
            'dealsByPhase' => $dealsByPhase,
            'totalDeals' => $totalDeals,
            'totalValue' => $totalValue,
        ])->layout('components.layouts.app', ['title' => 'Kanban']);
    }
}
