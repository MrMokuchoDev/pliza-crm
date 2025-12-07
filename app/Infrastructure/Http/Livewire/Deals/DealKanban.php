<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Domain\Deal\Services\DealPhaseService;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class DealKanban extends Component
{
    public string $search = '';

    // Value modal for closing as won
    public bool $showValueModal = false;

    public ?string $pendingWonDealId = null;

    public ?string $pendingWonPhaseId = null;

    public ?string $dealValue = null;

    #[On('dealSaved')]
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
        $deal = DealModel::with(['salePhase', 'lead'])->find($dealId);
        $newPhase = SalePhaseModel::find($phaseId);

        if (! $deal || ! $newPhase) {
            return;
        }

        $service = new DealPhaseService();
        $validation = $service->canChangePhase($deal, $newPhase);

        if (! $validation['can_change']) {
            if ($validation['reason'] === DealPhaseService::RESULT_NO_CHANGE) {
                return;
            }

            if ($validation['reason'] === DealPhaseService::RESULT_REQUIRES_VALUE) {
                $this->pendingWonDealId = $dealId;
                $this->pendingWonPhaseId = $phaseId;
                $this->dealValue = $deal->value ? (string) $deal->value : null;
                $this->showValueModal = true;

                return;
            }

            $this->dispatch('notify', type: 'error', message: $service->getErrorMessage($validation['reason']));

            return;
        }

        $result = $service->applyPhaseChange($deal, $newPhase);
        $this->dispatch('notify', type: 'success', message: $result['message']);
    }

    public function confirmWonWithValue(): void
    {
        $validationRules = DealPhaseService::getWonValueValidationRules();
        $this->validate($validationRules['rules'], $validationRules['messages']);

        $deal = DealModel::find($this->pendingWonDealId);
        $newPhase = SalePhaseModel::find($this->pendingWonPhaseId);

        if (! $deal || ! $newPhase) {
            $this->cancelWonPhase();

            return;
        }

        $service = new DealPhaseService();
        $result = $service->changePhaseWithValue($deal, $newPhase, (float) $this->dealValue);

        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);

        // Cerrar modal y limpiar
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }

    public function cancelWonPhase(): void
    {
        $this->showValueModal = false;
        $this->pendingWonDealId = null;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }

    public function render()
    {
        $openPhases = SalePhaseModel::where('is_closed', false)
            ->orderBy('order')
            ->get();

        $closedPhases = SalePhaseModel::where('is_closed', true)
            ->orderBy('order')
            ->get();

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

        $totalDeals = DealModel::whereIn('sale_phase_id', $openPhases->pluck('id'))->count();
        $totalValue = DealModel::whereIn('sale_phase_id', $openPhases->pluck('id'))->sum('value');

        return view('livewire.deals.kanban', [
            'openPhases' => $openPhases,
            'closedPhases' => $closedPhases,
            'dealsByPhase' => $dealsByPhase,
            'totalDeals' => $totalDeals,
            'totalValue' => $totalValue,
        ])->layout('components.layouts.app', ['title' => 'Pipeline']);
    }
}
