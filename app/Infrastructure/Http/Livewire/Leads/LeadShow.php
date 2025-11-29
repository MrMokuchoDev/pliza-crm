<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Leads;

use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\NoteModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadShow extends Component
{
    public ?LeadModel $lead = null;

    public string $leadId;

    public string $salePhaseId = '';

    // Note form
    public string $noteContent = '';

    public ?string $editingNoteId = null;

    public bool $showDeleteModal = false;

    public bool $showDeleteNoteModal = false;

    public ?string $deletingNoteId = null;

    protected function rules(): array
    {
        return [
            'noteContent' => 'required|string|min:1|max:5000',
        ];
    }

    public function mount(string $id): void
    {
        $this->leadId = $id;
        $this->loadLead();
    }

    public function loadLead(): void
    {
        $this->lead = LeadModel::with(['salePhase', 'notes', 'sourceSite'])->find($this->leadId);
        if ($this->lead) {
            $this->salePhaseId = $this->lead->sale_phase_id;
        }
    }

    public function updatePhase(): void
    {
        if (! $this->lead || $this->salePhaseId === $this->lead->sale_phase_id) {
            return;
        }

        $this->lead->update([
            'sale_phase_id' => $this->salePhaseId,
            'updated_at' => now(),
        ]);

        $this->loadLead();
        $this->dispatch('notify', type: 'success', message: 'Fase actualizada');
    }

    public function addNote(): void
    {
        $this->validate();

        if ($this->editingNoteId) {
            NoteModel::where('id', $this->editingNoteId)->update([
                'content' => $this->noteContent,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Nota actualizada');
        } else {
            NoteModel::create([
                'lead_id' => $this->leadId,
                'content' => $this->noteContent,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Nota agregada');
        }

        $this->resetNoteForm();
        $this->loadLead();
    }

    public function editNote(string $noteId): void
    {
        $note = NoteModel::find($noteId);
        if ($note) {
            $this->editingNoteId = $noteId;
            $this->noteContent = $note->content;
        }
    }

    public function cancelEditNote(): void
    {
        $this->resetNoteForm();
    }

    public function confirmDeleteNote(string $noteId): void
    {
        $this->deletingNoteId = $noteId;
        $this->showDeleteNoteModal = true;
    }

    public function deleteNote(): void
    {
        if ($this->deletingNoteId) {
            NoteModel::destroy($this->deletingNoteId);
            $this->dispatch('notify', type: 'success', message: 'Nota eliminada');
            $this->showDeleteNoteModal = false;
            $this->deletingNoteId = null;
            $this->loadLead();
        }
    }

    public function openEditModal(): void
    {
        $this->dispatch('openLeadModal', leadId: $this->leadId);
    }

    #[On('leadSaved')]
    public function onLeadSaved(): void
    {
        $this->loadLead();
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteLead(): void
    {
        if ($this->lead) {
            $this->lead->delete();
            $this->dispatch('notify', type: 'success', message: 'Lead eliminado');
            $this->redirect(route('leads.index'), navigate: true);
        }
    }

    private function resetNoteForm(): void
    {
        $this->noteContent = '';
        $this->editingNoteId = null;
    }

    public function render()
    {
        if (! $this->lead) {
            return view('livewire.leads.not-found')
                ->layout('components.layouts.app', ['title' => 'Lead no encontrado']);
        }

        $phases = SalePhaseModel::orderBy('order')->get();

        return view('livewire.leads.show', [
            'phases' => $phases,
        ])->layout('components.layouts.app', ['title' => $this->lead->name ?? 'Detalle del Lead']);
    }
}
