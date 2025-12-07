<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\SalePhases;

use App\Domain\SalePhase\Repositories\SalePhaseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
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

    protected SalePhaseRepositoryInterface $repository;

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

    public function boot(SalePhaseRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function mount(): void
    {
        $this->loadPhases();
    }

    public function loadPhases(): void
    {
        $this->phases = SalePhaseModel::orderBy('order')->get()->toArray();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $phase = SalePhaseModel::find($id);
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

        if ($this->editingId) {
            SalePhaseModel::where('id', $this->editingId)->update([
                'name' => $this->name,
                'color' => $this->color,
                'is_closed' => $this->isClosed,
                'is_won' => $this->isClosed ? $this->isWon : false,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Fase actualizada correctamente');
        } else {
            $maxOrder = SalePhaseModel::max('order') ?? 0;
            SalePhaseModel::create([
                'name' => $this->name,
                'color' => $this->color,
                'is_closed' => $this->isClosed,
                'is_won' => $this->isClosed ? $this->isWon : false,
                'is_default' => false,
                'order' => $maxOrder + 1,
            ]);
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

        $phase = SalePhaseModel::find($this->deletingId);
        if (! $phase) {
            return;
        }

        // Verificar que no sea la única fase activa
        $activeCount = SalePhaseModel::where('is_closed', false)->count();
        if (! $phase->is_closed && $activeCount <= 1) {
            $this->dispatch('notify', type: 'error', message: 'No puedes eliminar la única fase activa');

            return;
        }

        // Contar negocios en esta fase
        $dealsCount = \App\Infrastructure\Persistence\Eloquent\DealModel::where('sale_phase_id', $this->deletingId)->count();

        // Si hay negocios, validar fase destino
        if ($dealsCount > 0) {
            if (! $this->transferToPhaseId) {
                $this->dispatch('notify', type: 'error', message: 'Debes seleccionar una fase destino para transferir los ' . $dealsCount . ' negocio(s).');

                return;
            }

            // Validar que la fase destino exista y sea diferente
            $targetPhase = SalePhaseModel::find($this->transferToPhaseId);
            if (! $targetPhase) {
                $this->dispatch('notify', type: 'error', message: 'La fase destino seleccionada no existe.');

                return;
            }

            if ($this->transferToPhaseId === $this->deletingId) {
                $this->dispatch('notify', type: 'error', message: 'La fase destino no puede ser la misma que se está eliminando.');

                return;
            }

            // Transferir negocios
            \App\Infrastructure\Persistence\Eloquent\DealModel::where('sale_phase_id', $this->deletingId)
                ->update(['sale_phase_id' => $this->transferToPhaseId]);
        }

        // Si era default, asignar a otra fase
        if ($phase->is_default) {
            $newDefault = SalePhaseModel::where('id', '!=', $this->deletingId)
                ->where('is_closed', false)
                ->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $phase->delete();

        $this->dispatch('notify', type: 'success', message: 'Fase eliminada correctamente');
        $this->closeDeleteModal();
        $this->loadPhases();
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            SalePhaseModel::where('id', $id)->update(['order' => $index + 1]);
        }
        $this->loadPhases();
        $this->dispatch('notify', type: 'success', message: 'Orden actualizado');
    }

    public function setAsDefault(string $id): void
    {
        SalePhaseModel::where('is_default', true)->update(['is_default' => false]);
        SalePhaseModel::where('id', $id)->update(['is_default' => true]);
        $this->loadPhases();
        $this->dispatch('notify', type: 'success', message: 'Fase por defecto actualizada');
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
