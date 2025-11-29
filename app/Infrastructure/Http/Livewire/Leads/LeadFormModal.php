<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadFormModal extends Component
{
    public bool $show = false;

    public ?string $leadId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    public string $salePhaseId = '';

    protected function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
            'salePhaseId' => 'required|exists:sale_phases,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'salePhaseId.required' => 'La fase de venta es requerida.',
            'email.email' => 'El email debe ser válido.',
        ];
    }

    public function mount(): void
    {
        $this->setDefaultPhase();
    }

    #[On('openLeadModal')]
    public function open(?string $leadId = null): void
    {
        $this->resetForm();

        if ($leadId) {
            $lead = LeadModel::find($leadId);
            if ($lead) {
                $this->leadId = $leadId;
                $this->name = $lead->name ?? '';
                $this->email = $lead->email ?? '';
                $this->phone = $lead->phone ?? '';
                $this->message = $lead->message ?? '';
                $this->salePhaseId = $lead->sale_phase_id;
            }
        }

        $this->show = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->leadId) {
            LeadModel::where('id', $this->leadId)->update([
                'name' => $this->name ?: null,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'message' => $this->message ?: null,
                'sale_phase_id' => $this->salePhaseId,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Lead actualizado');
        } else {
            LeadModel::create([
                'name' => $this->name ?: null,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'message' => $this->message ?: null,
                'source_type' => SourceType::MANUAL->value,
                'sale_phase_id' => $this->salePhaseId,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Lead creado');
        }

        $this->close();
        $this->dispatch('leadSaved');
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->leadId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->message = '';
        $this->setDefaultPhase();
        $this->resetValidation();
    }

    private function setDefaultPhase(): void
    {
        $defaultPhase = SalePhaseModel::where('is_default', true)->first();
        $this->salePhaseId = $defaultPhase?->id ?? '';
    }

    public function render()
    {
        $phases = SalePhaseModel::orderBy('order')->get();

        return view('livewire.leads.form-modal', [
            'phases' => $phases,
        ]);
    }
}
