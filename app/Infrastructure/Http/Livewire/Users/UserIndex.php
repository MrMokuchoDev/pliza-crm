<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Users;

use App\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // La verificación de acceso se hace en el middleware de la ruta
    }

    public string $search = '';

    public string $filterRole = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingId = null;

    public ?string $deletingId = null;

    public string $deletingName = '';

    // Campos del formulario
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $role_id = null;

    public bool $is_active = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRole' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['email'] = 'required|email|max:255|unique:users,email,'.$this->editingId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        if (! Auth::user()?->canCreateUsers()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear usuarios');

            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        if (! Auth::user()?->canUpdateUsers()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar usuarios');

            return;
        }

        $user = User::find($id);
        if (! $user) {
            $this->dispatch('notify', type: 'error', message: 'Usuario no encontrado');

            return;
        }

        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->is_active = $user->is_active ?? true;
        $this->password = '';
        $this->password_confirmation = '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Verificación adicional de seguridad
        $currentUser = Auth::user();
        if ($this->editingId) {
            if (! $currentUser?->canUpdateUsers()) {
                $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar usuarios');
                $this->closeModal();

                return;
            }
        } else {
            if (! $currentUser?->canCreateUsers()) {
                $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear usuarios');
                $this->closeModal();

                return;
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::find($this->editingId);
            if ($user) {
                $user->update($data);
                $this->dispatch('notify', type: 'success', message: 'Usuario actualizado correctamente');
            }
        } else {
            User::create($data);
            $this->dispatch('notify', type: 'success', message: 'Usuario creado correctamente');
        }

        $this->closeModal();
    }

    public function openDeleteModal(string $id): void
    {
        if (! Auth::user()?->canDeleteUsers()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar usuarios');

            return;
        }

        $user = User::find($id);
        if (! $user) {
            $this->dispatch('notify', type: 'error', message: 'Usuario no encontrado');

            return;
        }

        // No permitir eliminar el usuario actual
        if ($user->id === Auth::id()) {
            $this->dispatch('notify', type: 'error', message: 'No puedes eliminar tu propio usuario');

            return;
        }

        $this->deletingId = $id;
        $this->deletingName = $user->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        // Verificación adicional de seguridad
        if (! Auth::user()?->canDeleteUsers()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar usuarios');
            $this->closeDeleteModal();

            return;
        }

        $user = User::find($this->deletingId);
        if ($user && $user->id !== Auth::id()) {
            $user->delete();
            $this->dispatch('notify', type: 'success', message: 'Usuario eliminado correctamente');
        }

        $this->closeDeleteModal();
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
        $this->deletingName = '';
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterRole = '';
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role_id = null;
        $this->is_active = true;
        $this->resetValidation();
    }

    #[On('userSaved')]
    public function refreshList(): void
    {
        // Lista se refresca automáticamente
    }

    public function render()
    {
        $query = User::with('role');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterRole) {
            $query->where('role_id', $this->filterRole);
        }

        $users = $query->orderBy('name')->paginate(10);
        $roles = RoleModel::orderBy('level', 'desc')->get();
        $currentUser = Auth::user();

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => $roles,
            'canCreate' => $currentUser?->canCreateUsers() ?? false,
            'canUpdate' => $currentUser?->canUpdateUsers() ?? false,
            'canDelete' => $currentUser?->canDeleteUsers() ?? false,
        ])->layout('components.layouts.app', ['title' => 'Usuarios']);
    }
}
