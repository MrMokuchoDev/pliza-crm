<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Deals;

use App\Application\CustomField\Services\CustomFieldService;
use App\Application\Deal\Services\DealService;
use App\Application\DealComment\DTOs\DealCommentData;
use App\Application\DealComment\Services\DealCommentService;
use App\Application\SalePhase\Services\SalePhaseService;
use App\Domain\Deal\Services\DealPhaseService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class DealShow extends Component
{
    public $deal = null;

    public string $dealId;

    public array $customFieldGroups = [];

    public string $salePhaseId = '';

    public int $phaseSelectKey = 0;

    public bool $canEditDeal = false;

    public bool $canDeleteDeal = false;

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
        // Verificar acceso al módulo
        if (! Auth::user()?->canAccessDeals()) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        $this->dealId = $id;
        $this->loadDeal();
    }

    public function loadDeal(): void
    {
        $dealService = app(DealService::class);
        $this->deal = $dealService->findForShow($this->dealId);
        if ($this->deal) {
            $this->salePhaseId = $this->deal->sale_phase_id;
            $this->calculateDealPermissions();
            $this->loadCustomFields();
        }
    }

    /**
     * Carga los custom fields agrupados por bloques con sus valores.
     */
    protected function loadCustomFields(): void
    {
        if (!$this->deal) {
            return;
        }

        $customFieldService = app(CustomFieldService::class);

        // Obtener grupos de custom fields para deals
        $groups = $customFieldService->getGroupsByEntity('deal');

        // Cargar valores actuales del deal
        $customFieldValues = $this->deal->customFieldValues ?? collect();

        $this->customFieldGroups = [];

        foreach ($groups as $group) {
            // Obtener campos del grupo
            $fields = $customFieldService->getFieldsByEntity('deal', true, $group->id);

            // Agregar valores a los campos
            $fieldsWithValues = [];
            foreach ($fields as $field) {
                // Excluir campos que ya se muestran en otras secciones
                // cf_deal_1 = Nombre del Negocio (header)
                // cf_deal_2 = Descripción (card propio)
                // cf_deal_3 = Valor (card Detalles)
                // cf_deal_4 = Fecha Estimada de Cierre (card Detalles)
                if (in_array($field->name, ['cf_deal_1', 'cf_deal_2', 'cf_deal_3', 'cf_deal_4'])) {
                    continue;
                }

                $value = $customFieldValues->firstWhere('custom_field_id', $field->id);

                // Determinar si el campo necesita opciones
                $needsOptions = in_array($field->type, ['select', 'multiselect', 'radio', 'checkbox']);

                // Obtener opciones y convertirlas a arrays simples
                $options = [];
                if ($needsOptions) {
                    $optionDtos = $customFieldService->getOptions($field->id);
                    foreach ($optionDtos as $optionDto) {
                        $options[] = [
                            'value' => $optionDto->value,
                            'label' => $optionDto->label,
                        ];
                    }
                }

                $fieldsWithValues[] = [
                    'field' => $field->toArray(), // Convertir DTO a array
                    'value' => $value?->value,
                    'options' => $options,
                ];
            }

            // Solo agregar grupos que tengan campos
            if (count($fieldsWithValues) > 0) {
                $this->customFieldGroups[] = [
                    'group' => $group->toArray(), // Convertir DTO a array
                    'fields' => $fieldsWithValues,
                ];
            }
        }
    }

    /**
     * Calcula si el usuario puede editar/eliminar el deal actual.
     */
    protected function calculateDealPermissions(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $this->deal) {
            $this->canEditDeal = false;
            $this->canDeleteDeal = false;

            return;
        }

        // Usar los métodos centralizados del modelo User
        $this->canEditDeal = $user->canEditDeal($this->deal->assigned_to);
        $this->canDeleteDeal = $user->canDeleteDeal($this->deal->assigned_to);
    }

    public function updatePhase(): void
    {
        if (! $this->deal || $this->salePhaseId === $this->deal->sale_phase_id) {
            return;
        }

        // Validación de seguridad: verificar permisos antes de cambiar fase
        if (!$this->canEditDeal) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para modificar este negocio.');
            $this->salePhaseId = $this->deal->sale_phase_id;
            $this->phaseSelectKey++;
            return;
        }

        $phaseService = app(SalePhaseService::class);
        $newPhase = $phaseService->find($this->salePhaseId);
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

        $phaseService = app(SalePhaseService::class);
        $newPhase = $phaseService->find($this->pendingWonPhaseId);
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
        // Validación de seguridad: verificar permisos antes de eliminar
        if (!$this->canDeleteDeal) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar este negocio.');
            $this->showDeleteModal = false;
            return;
        }

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

        $phaseService = app(SalePhaseService::class);
        $phases = $phaseService->getAllOrdered();

        return view('livewire.deals.show', [
            'phases' => $phases,
        ])->layout('components.layouts.app', ['title' => 'Detalle del Negocio']);
    }
}
