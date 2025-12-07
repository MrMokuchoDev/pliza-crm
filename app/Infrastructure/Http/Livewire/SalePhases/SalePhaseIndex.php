<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\SalePhases;

use App\Application\SalePhase\DTOs\SalePhaseData;
use App\Application\SalePhase\Services\SalePhaseService;
use Livewire\Component;

class SalePhaseIndex extends Component
{
    public array $phases = [];

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingId = null;

    public ?string $deletingId = null;

    public string $name = '';

    public string $color = '#6B7280';

    public bool $isClosed = false;

    public bool $isWon = false;

    public ?string $transferToPhaseId = null;

    protected $listeners = ['refreshPhases' => 'loadPhases', 'phasesReordered' => 'updateOrder'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'isClosed' => 'boolean',
            'isWon' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->loadPhases();
    }

    public function loadPhases(): void
    {
        $service = app(SalePhaseService::class);
        $this->phases = $service->getAllOrdered()->toArray();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $service = app(SalePhaseService::class);
        $phase = $service->find($id);

        if ($phase) {
            $this->editingId = $id;
            $this->name = $phase->name;
            $this->color = $phase->color;
            $this->isClosed = $phase->is_closed;
            $this->isWon = $phase->is_won;
            $this->showModal = true;
        }
    }

    public function save(): void
    {
        $this->validate();

        $service = app(SalePhaseService::class);

        $data = new SalePhaseData(
            name: $this->name,
            color: $this->color,
            isClosed: $this->isClosed,
            isWon: $this->isClosed ? $this->isWon : false,
        );

        if ($this->editingId) {
            $service->update($this->editingId, $data);
            $this->dispatch('notify', type: 'success', message: 'Fase actualizada correctamente');
        } else {
            $service->create($data);
            $this->dispatch('notify', type: 'success', message: 'Fase creada correctamente');
        }

        $this->closeModal();
        $this->loadPhases();
    }

    public function openDeleteModal(string $id): void
    {
        $this->deletingId = $id;
        $this->transferToPhaseId = null;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $service = app(SalePhaseService::class);

        $result = $service->delete($this->deletingId, $this->transferToPhaseId);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: 'Fase eliminada correctamente');
            $this->closeDeleteModal();
            $this->loadPhases();
        } else {
            $this->dispatch('notify', type: 'error', message: $result['error'] ?? 'Error al eliminar la fase');
        }
    }

    public function updateOrder(array $orderedIds): void
    {
        $service = app(SalePhaseService::class);
        $service->reorder($orderedIds);

        $this->loadPhases();
        $this->dispatch('notify', type: 'success', message: 'Orden actualizado');
    }

    public function setAsDefault(string $id): void
    {
        $service = app(SalePhaseService::class);

        if ($service->setAsDefault($id)) {
            $this->loadPhases();
            $this->dispatch('notify', type: 'success', message: 'Fase por defecto actualizada');
        } else {
            $this->dispatch('notify', type: 'error', message: 'No se puede establecer una fase cerrada como default');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->transferToPhaseId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#6B7280';
        $this->isClosed = false;
        $this->isWon = false;
    }

    public function render()
    {
        $availablePhases = collect($this->phases)
            ->filter(fn ($p) => $p['id'] !== $this->deletingId)
            ->values()
            ->toArray();

        return view('livewire.sale-phases.index', [
            'availablePhases' => $availablePhases,
        ])->layout('components.layouts.app', ['title' => 'Fases de Venta']);
    }
}
