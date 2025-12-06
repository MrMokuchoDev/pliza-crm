<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class DealFormModal extends Component
{
    public bool $show = false;

    public ?string $dealId = null;

    public ?string $leadId = null;

    public string $name = '';

    public ?string $value = '';

    public string $description = '';

    public string $salePhaseId = '';

    public ?string $estimatedCloseDate = null;

    public ?string $closeDate = null;

    // Lead data for display/edit
    public string $leadName = '';

    public string $leadEmail = '';

    public string $leadPhone = '';

    // Lead search
    public string $leadSearch = '';

    public bool $showLeadSearch = false;

    public bool $createNewLead = false;

    public ?string $leadHasOpenDealError = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'salePhaseId' => 'required|exists:sale_phases,id',
            'estimatedCloseDate' => 'nullable|date',
            'closeDate' => 'nullable|date',
            'leadName' => 'nullable|string|max:255',
            'leadEmail' => 'nullable|email|max:255',
            'leadPhone' => 'nullable|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del negocio es requerido.',
            'salePhaseId.required' => 'La fase de venta es requerida.',
            'value.numeric' => 'El valor debe ser un número.',
            'leadEmail.email' => 'El email debe ser válido.',
        ];
    }

    #[On('openDealModal')]
    public function open(?string $dealId = null, ?string $leadId = null): void
    {
        $this->resetForm();

        if ($dealId) {
            $deal = DealModel::with('lead')->find($dealId);
            if ($deal) {
                $this->dealId = $dealId;
                $this->leadId = $deal->lead_id;
                $this->name = $deal->name;
                $this->value = $deal->value ? (string) $deal->value : '';
                $this->description = $deal->description ?? '';
                $this->salePhaseId = $deal->sale_phase_id;
                $this->estimatedCloseDate = $deal->estimated_close_date?->format('Y-m-d');
                $this->closeDate = $deal->close_date?->format('Y-m-d');

                // Load lead data
                if ($deal->lead) {
                    $this->leadName = $deal->lead->name ?? '';
                    $this->leadEmail = $deal->lead->email ?? '';
                    $this->leadPhone = $deal->lead->phone ?? '';
                }
                $this->showLeadSearch = false;
            }
        } elseif ($leadId) {
            // Creating new deal for existing lead
            $lead = LeadModel::find($leadId);
            if ($lead) {
                // Check if lead already has an open deal
                if ($lead->hasOpenDeal()) {
                    $this->dispatch('notify', type: 'error', message: 'Este contacto ya tiene un negocio abierto.');

                    return;
                }

                $this->leadId = $leadId;
                $this->leadName = $lead->name ?? '';
                $this->leadEmail = $lead->email ?? '';
                $this->leadPhone = $lead->phone ?? '';
                $this->showLeadSearch = false;
            }
        } else {
            // Opening modal without lead - show search
            $this->showLeadSearch = true;
            $this->createNewLead = false;
        }

        $this->show = true;
    }

    public function updatedLeadSearch(): void
    {
        $this->leadHasOpenDealError = null;
    }

    public function selectLead(string $id): void
    {
        $lead = LeadModel::find($id);
        if (! $lead) {
            return;
        }

        // Check if lead has open deal
        if ($lead->hasOpenDeal()) {
            $this->leadHasOpenDealError = 'Este contacto ya tiene un negocio abierto.';

            return;
        }

        $this->leadId = $id;
        $this->leadName = $lead->name ?? '';
        $this->leadEmail = $lead->email ?? '';
        $this->leadPhone = $lead->phone ?? '';
        $this->showLeadSearch = false;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
    }

    public function startNewLead(): void
    {
        $this->createNewLead = true;
        $this->showLeadSearch = false;
        $this->leadId = null;
        $this->leadSearch = '';
        $this->leadHasOpenDealError = null;
    }

    public function backToSearch(): void
    {
        $this->createNewLead = false;
        $this->showLeadSearch = true;
        $this->leadId = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->name = '';
    }

    public function clearSelectedLead(): void
    {
        $this->leadId = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->name = '';
        $this->showLeadSearch = true;
        $this->createNewLead = false;
    }

    public function save(): void
    {
        // Verificar PRIMERO si se quiere cerrar como GANADO sin valor
        $phase = SalePhaseModel::find($this->salePhaseId);
        if ($phase?->is_closed && $phase?->is_won) {
            if (empty($this->value) || ! is_numeric($this->value)) {
                $this->addError('value', 'El valor del negocio es obligatorio para cerrarlo como ganado.');
                $this->dispatch('notify', type: 'error', message: 'Debes ingresar el valor del negocio para cerrarlo como ganado.');

                return;
            }
        }

        $this->validate();

        // If creating new lead
        if ($this->createNewLead && ! $this->leadId) {
            $newLead = LeadModel::create([
                'name' => $this->leadName ?: null,
                'email' => $this->leadEmail ?: null,
                'phone' => $this->leadPhone ?: null,
                'source_type' => SourceType::MANUAL,
            ]);
            $this->leadId = $newLead->id;
        }

        // Update lead data if we have a lead
        if ($this->leadId && ! $this->createNewLead) {
            LeadModel::where('id', $this->leadId)->update([
                'name' => $this->leadName ?: null,
                'email' => $this->leadEmail ?: null,
                'phone' => $this->leadPhone ?: null,
            ]);
        }

        // Si estamos editando un negocio cerrado y se quiere mover a fase abierta,
        // verificar que el contacto no tenga otro negocio abierto
        if ($this->dealId) {
            $deal = DealModel::with(['salePhase', 'lead'])->find($this->dealId);
            if ($deal && $deal->salePhase?->is_closed && ! $phase?->is_closed) {
                if ($deal->lead && $deal->lead->hasOpenDeal($this->dealId)) {
                    $this->dispatch('notify', type: 'error', message: 'Este contacto ya tiene un negocio abierto. Cierra o elimina el otro negocio antes de reabrir este.');

                    return;
                }
            }
        }

        // Si se mueve a fase abierta, limpiar fecha de cierre
        $closeDate = $phase?->is_closed
            ? ($this->closeDate ?: now()->format('Y-m-d'))
            : null;

        if ($this->dealId) {
            DealModel::where('id', $this->dealId)->update([
                'name' => $this->name,
                'value' => $this->value ?: null,
                'description' => $this->description ?: null,
                'sale_phase_id' => $this->salePhaseId,
                'estimated_close_date' => $this->estimatedCloseDate ?: null,
                'close_date' => $closeDate,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Negocio actualizado');
        } else {
            DealModel::create([
                'lead_id' => $this->leadId,
                'name' => $this->name,
                'value' => $this->value ?: null,
                'description' => $this->description ?: null,
                'sale_phase_id' => $this->salePhaseId,
                'estimated_close_date' => $this->estimatedCloseDate ?: null,
                'close_date' => $closeDate,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Negocio creado');
        }

        $this->close();
        $this->dispatch('dealSaved');
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->dealId = null;
        $this->leadId = null;
        $this->name = '';
        $this->value = '';
        $this->description = '';
        $this->estimatedCloseDate = null;
        $this->closeDate = null;
        $this->leadName = '';
        $this->leadEmail = '';
        $this->leadPhone = '';
        $this->leadSearch = '';
        $this->showLeadSearch = false;
        $this->createNewLead = false;
        $this->leadHasOpenDealError = null;
        $this->setDefaultPhase();
        $this->resetValidation();
    }

    private function setDefaultPhase(): void
    {
        $defaultPhase = SalePhaseModel::where('is_default', true)->first()
            ?? SalePhaseModel::where('is_closed', false)->orderBy('order')->first();
        $this->salePhaseId = $defaultPhase?->id ?? '';
    }

    public function render()
    {
        $phases = SalePhaseModel::orderBy('order')->get();

        // Search leads if searching
        $searchResults = collect();
        if ($this->showLeadSearch && strlen($this->leadSearch) >= 2) {
            $searchResults = LeadModel::query()
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->leadSearch}%")
                        ->orWhere('email', 'like', "%{$this->leadSearch}%")
                        ->orWhere('phone', 'like', "%{$this->leadSearch}%");
                })
                ->withCount(['activeDeals'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        return view('livewire.deals.form-modal', [
            'phases' => $phases,
            'searchResults' => $searchResults,
        ]);
    }
}
