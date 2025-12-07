<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
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

    protected function rules(): array
    {
        $emailUniqueRule = 'unique:leads,email';
        if ($this->leadId) {
            $emailUniqueRule .= ',' . $this->leadId;
        }

        return [
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', $emailUniqueRule],
            'phone' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'message' => 'nullable|string|max:5000',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado en otro contacto.',
            'phone.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'phone.regex' => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
        ];
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
            }
        }

        $this->show = true;
    }

    public function save(): void
    {
        // Validar que al menos uno de los datos de contacto esté presente
        if (empty($this->email) && empty($this->phone)) {
            $this->addError('email', 'Debes proporcionar al menos un email o teléfono.');
            $this->addError('phone', 'Debes proporcionar al menos un email o teléfono.');

            return;
        }

        $this->validate();

        if ($this->leadId) {
            LeadModel::where('id', $this->leadId)->update([
                'name' => $this->name ?: null,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'message' => $this->message ?: null,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Contacto actualizado');
        } else {
            LeadModel::create([
                'name' => $this->name ?: null,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'message' => $this->message ?: null,
                'source_type' => SourceType::MANUAL->value,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Contacto creado');
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
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.leads.form-modal');
    }
}
