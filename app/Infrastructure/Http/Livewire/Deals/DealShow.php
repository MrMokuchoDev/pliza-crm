<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\Deal\Services\DealService;
use App\Application\DealComment\DTOs\DealCommentData;
use App\Application\DealComment\Services\DealCommentService;
use App\Domain\Deal\Services\DealPhaseService;
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

    // Value modal for closing as won
    public bool $showValueModal = false;

    public ?string $pendingWonPhaseId = null;

    public ?string $dealValue = null;

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
        if (! $newPhase) {
            return;
        }

        $service = new DealPhaseService();
        $validation = $service->canChangePhase($this->deal, $newPhase);

        if (! $validation['can_change']) {
            if ($validation['reason'] === DealPhaseService::RESULT_REQUIRES_VALUE) {
                $this->pendingWonPhaseId = $this->salePhaseId;
                $this->dealValue = $this->deal->value ? (string) $this->deal->value : null;
                $this->showValueModal = true;
            } else {
                $this->dispatch('notify', type: 'error', message: $service->getErrorMessage($validation['reason']));
            }

            // Revertir selección visual
            $this->salePhaseId = $this->deal->sale_phase_id;
            $this->phaseSelectKey++;

            return;
        }

        $result = $service->applyPhaseChange($this->deal, $newPhase);
        $this->loadDeal();
        $this->dispatch('notify', type: 'success', message: $result['message']);
    }

    public function confirmWonWithValue(): void
    {
        $validationRules = DealPhaseService::getWonValueValidationRules();
        $this->validate($validationRules['rules'], $validationRules['messages']);

        $newPhase = SalePhaseModel::find($this->pendingWonPhaseId);
        if (! $newPhase) {
            $this->cancelWonPhase();

            return;
        }

        $service = new DealPhaseService();
        $result = $service->changePhaseWithValue($this->deal, $newPhase, (float) $this->dealValue);

        $this->loadDeal();
        $this->dispatch('notify', type: $result['success'] ? 'success' : 'error', message: $result['message']);

        // Cerrar modal y limpiar
        $this->showValueModal = false;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
    }

    public function cancelWonPhase(): void
    {
        $this->showValueModal = false;
        $this->pendingWonPhaseId = null;
        $this->dealValue = null;
        $this->salePhaseId = $this->deal->sale_phase_id;
        $this->phaseSelectKey++;
    }

    public function addComment(): void
    {
        $this->validate();

        $commentService = app(DealCommentService::class);

        if ($this->editingCommentId) {
            $data = new DealCommentData(
                content: $this->commentContent,
                type: $this->commentType,
            );
            $commentService->update($this->editingCommentId, $data);
            $this->dispatch('notify', type: 'success', message: 'Comentario actualizado');
        } else {
            $data = new DealCommentData(
                dealId: $this->dealId,
                content: $this->commentContent,
                type: $this->commentType,
            );
            $commentService->create($data);
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
        $commentService = app(DealCommentService::class);
        $comment = $commentService->find($commentId);
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
            $commentService = app(DealCommentService::class);
            $commentService->delete($this->deletingCommentId);
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
            $dealService = app(DealService::class);
            $result = $dealService->delete($this->dealId);

            if ($result['success']) {
                $message = 'Negocio eliminado';
                if ($result['deleted_comments'] > 0) {
                    $message .= " ({$result['deleted_comments']} comentarios eliminados)";
                }
                $this->dispatch('notify', type: 'success', message: $message);
            }

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
