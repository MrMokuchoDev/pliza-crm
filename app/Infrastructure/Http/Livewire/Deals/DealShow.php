<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Infrastructure\Persistence\Eloquent\DealCommentModel;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Livewire\Attributes\On;
use Livewire\Component;

class DealShow extends Component
{
    public ?DealModel $deal = null;

    public string $dealId;

    public string $salePhaseId = '';

    public int $phaseSelectKey = 0;

    // Comment form
    public string $commentContent = '';

    public string $commentType = 'general';

    public ?string $editingCommentId = null;

    public bool $showDeleteModal = false;

    public bool $showDeleteCommentModal = false;

    public ?string $deletingCommentId = null;

    protected function rules(): array
    {
        return [
            'commentContent' => 'required|string|min:1|max:5000',
            'commentType' => 'required|in:general,call,whatsapp,email',
        ];
    }

    public function mount(string $id): void
    {
        $this->dealId = $id;
        $this->loadDeal();
    }

    public function loadDeal(): void
    {
        $this->deal = DealModel::with(['lead', 'salePhase', 'comments'])->find($this->dealId);
        if ($this->deal) {
            $this->salePhaseId = $this->deal->sale_phase_id;
        }
    }

    public function updatePhase(): void
    {
        if (! $this->deal || $this->salePhaseId === $this->deal->sale_phase_id) {
            return;
        }

        $newPhase = SalePhaseModel::find($this->salePhaseId);
        $currentPhase = $this->deal->salePhase;

        // Si el negocio está cerrado y se quiere mover a una fase abierta,
        // verificar que el contacto no tenga otro negocio abierto
        if ($currentPhase?->is_closed && ! $newPhase?->is_closed) {
            if ($this->deal->lead && $this->deal->lead->hasOpenDeal($this->deal->id)) {
                $this->salePhaseId = $this->deal->sale_phase_id; // Revertir selección
                $this->phaseSelectKey++; // Forzar re-render del select
                $this->dispatch('notify', type: 'error', message: 'Este contacto ya tiene un negocio abierto. Cierra o elimina el otro negocio antes de reabrir este.');

                return;
            }
        }

        $updateData = [
            'sale_phase_id' => $this->salePhaseId,
            'updated_at' => now(),
        ];

        // Si se mueve a fase cerrada, establecer fecha de cierre
        if ($newPhase?->is_closed && ! $this->deal->close_date) {
            $updateData['close_date'] = now();
        }

        // Si se mueve a fase abierta, limpiar fecha de cierre
        if (! $newPhase?->is_closed) {
            $updateData['close_date'] = null;
        }

        $this->deal->update($updateData);
        $this->loadDeal();

        $message = $newPhase?->is_closed
            ? ($newPhase->is_won ? 'Negocio marcado como ganado' : 'Negocio marcado como perdido')
            : 'Fase actualizada';

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function addComment(): void
    {
        $this->validate();

        if ($this->editingCommentId) {
            DealCommentModel::where('id', $this->editingCommentId)->update([
                'content' => $this->commentContent,
                'type' => $this->commentType,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Comentario actualizado');
        } else {
            DealCommentModel::create([
                'deal_id' => $this->dealId,
                'content' => $this->commentContent,
                'type' => $this->commentType,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Comentario agregado');
        }

        $this->resetCommentForm();
        $this->loadDeal();
    }

    public function setCommentType(string $type): void
    {
        $this->commentType = $type;
    }

    public function editComment(string $commentId): void
    {
        $comment = DealCommentModel::find($commentId);
        if ($comment) {
            $this->editingCommentId = $commentId;
            $this->commentContent = $comment->content;
            $this->commentType = $comment->type;
        }
    }

    public function cancelEditComment(): void
    {
        $this->resetCommentForm();
    }

    public function confirmDeleteComment(string $commentId): void
    {
        $this->deletingCommentId = $commentId;
        $this->showDeleteCommentModal = true;
    }

    public function deleteComment(): void
    {
        if ($this->deletingCommentId) {
            DealCommentModel::destroy($this->deletingCommentId);
            $this->dispatch('notify', type: 'success', message: 'Comentario eliminado');
            $this->showDeleteCommentModal = false;
            $this->deletingCommentId = null;
            $this->loadDeal();
        }
    }

    public function openEditModal(): void
    {
        $this->dispatch('openDealModal', dealId: $this->dealId);
    }

    #[On('dealSaved')]
    public function onDealSaved(): void
    {
        $this->loadDeal();
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteDeal(): void
    {
        if ($this->deal) {
            $this->deal->delete();
            $this->dispatch('notify', type: 'success', message: 'Negocio eliminado');
            $this->redirect(route('deals.index'), navigate: true);
        }
    }

    private function resetCommentForm(): void
    {
        $this->commentContent = '';
        $this->commentType = 'general';
        $this->editingCommentId = null;
    }

    public function render()
    {
        if (! $this->deal) {
            return view('livewire.deals.not-found')
                ->layout('components.layouts.app', ['title' => 'Negocio no encontrado']);
        }

        $phases = SalePhaseModel::orderBy('order')->get();

        return view('livewire.deals.show', [
            'phases' => $phases,
        ])->layout('components.layouts.app', ['title' => $this->deal->name ?? 'Detalle del Negocio']);
    }
}
