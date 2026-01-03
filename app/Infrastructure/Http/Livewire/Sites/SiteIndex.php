<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Sites;

use App\Application\Site\DTOs\SiteData;
use App\Application\Site\Services\SiteService;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class SiteIndex extends Component
{
    // Form fields
    public bool $showModal = false;

    public ?string $siteId = null;

    public string $name = '';

    public string $domain = '';

    public bool $isActive = true;

    // Lead Assignment settings
    public ?string $defaultUserId = null;

    public Collection $availableUsers;

    // Widget settings
    public string $widgetType = 'whatsapp';

    public string $widgetPhone = '';

    public string $widgetPosition = 'bottom-right';

    public string $widgetColor = '#3B82F6';

    public string $widgetTitle = 'Contactanos';

    public string $widgetButtonText = 'Enviar';

    public string $privacyPolicyUrl = '';

    public function mount(): void
    {
        // La verificación de acceso se hace en el middleware de la ruta
        $this->availableUsers = collect();
        $this->loadAvailableUsers();
    }

    protected function loadAvailableUsers(): void
    {
        $this->availableUsers = User::where('is_active', true)
            ->orderBy('name')
            ->get(['uuid', 'name', 'email']);
    }

    // Delete modal
    public bool $showDeleteModal = false;

    public ?string $deletingSiteId = null;

    // Embed code modal
    public bool $showEmbedModal = false;

    public $embedSite = null;

    public string $selectedWidgetType = 'whatsapp';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'privacyPolicyUrl' => 'required|url|max:500',
            'defaultUserId' => 'nullable|exists:users,uuid',
            'widgetType' => 'required|in:whatsapp,phone,contact_form',
            'widgetPhone' => 'required|string|max:50',
            'widgetPosition' => 'required|in:bottom-right,bottom-left,top-right,top-left',
            'widgetColor' => 'required|string|max:20',
            'widgetTitle' => 'required|string|max:255',
            'widgetButtonText' => 'required|string|max:100',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre del sitio',
            'domain' => 'dominio',
            'privacyPolicyUrl' => 'URL de política de privacidad',
            'defaultUserId' => 'usuario por defecto',
            'widgetType' => 'tipo de widget',
            'widgetPhone' => 'teléfono del negocio',
            'widgetPosition' => 'posición del widget',
            'widgetColor' => 'color',
            'widgetTitle' => 'título del modal',
            'widgetButtonText' => 'texto del botón',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'url' => 'El campo :attribute debe ser una URL válida.',
            'max' => 'El campo :attribute no puede tener más de :max caracteres.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'in' => 'El :attribute seleccionado no es válido.',
        ];
    }

    public function openCreateModal(): void
    {
        if (! auth()->user()?->canCreateSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear sitios');

            return;
        }

        $this->resetForm();
        $this->loadAvailableUsers();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        if (! auth()->user()?->canUpdateSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar sitios');

            return;
        }

        $service = app(SiteService::class);
        $site = $service->find($id);

        if ($site) {
            $this->siteId = $id;
            $this->name = $site->name;
            $this->domain = $site->domain;
            $this->isActive = $site->is_active;
            $this->defaultUserId = $site->default_user_id;
            $this->privacyPolicyUrl = $site->privacy_policy_url ?? '';

            $settings = $site->settings ?? [];
            $this->widgetType = $settings['type'] ?? 'whatsapp';
            $this->widgetPhone = $settings['phone'] ?? '';
            $this->widgetPosition = $settings['position'] ?? 'bottom-right';
            $this->widgetColor = $settings['color'] ?? '#3B82F6';
            $this->widgetTitle = $settings['title'] ?? 'Contactanos';
            $this->widgetButtonText = $settings['button_text'] ?? 'Enviar';

            // Recargar usuarios disponibles
            $this->loadAvailableUsers();

            $this->showModal = true;
        }
    }

    public function save(): void
    {
        // Verificar permiso según si es crear o actualizar
        if ($this->siteId) {
            if (! auth()->user()?->canUpdateSites()) {
                $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar sitios');

                return;
            }
        } else {
            if (! auth()->user()?->canCreateSites()) {
                $this->dispatch('notify', type: 'error', message: 'No tienes permiso para crear sitios');

                return;
            }
        }

        $this->validate();

        $service = app(SiteService::class);

        $settings = [
            'type' => $this->widgetType,
            'phone' => $this->widgetPhone,
            'position' => $this->widgetPosition,
            'color' => $this->widgetColor,
            'title' => $this->widgetTitle,
            'button_text' => $this->widgetButtonText,
        ];

        $data = new SiteData(
            name: $this->name,
            domain: $this->domain,
            isActive: $this->isActive,
            settings: $settings,
            privacyPolicyUrl: $this->privacyPolicyUrl ?: null,
            defaultUserId: $this->defaultUserId ?: null,
            clearDefaultUser: empty($this->defaultUserId),
        );

        try {
            if ($this->siteId) {
                $service->update($this->siteId, $data);
                $this->dispatch('notify', type: 'success', message: 'Sitio actualizado');
            } else {
                $service->create($data);
                $this->dispatch('notify', type: 'success', message: 'Sitio creado');
            }

            $this->closeModal();
        } catch (\InvalidArgumentException $e) {
            $this->addError('privacyPolicyUrl', $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        if (! auth()->user()?->canUpdateSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para modificar sitios');

            return;
        }

        $service = app(SiteService::class);
        $result = $service->toggleActive($id);

        if ($result['success']) {
            $message = $result['is_active'] ? 'Sitio activado' : 'Sitio desactivado';
            $this->dispatch('notify', type: 'success', message: $message);
        }
    }

    public function regenerateApiKey(string $id): void
    {
        if (! auth()->user()?->canUpdateSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para modificar sitios');

            return;
        }

        $service = app(SiteService::class);
        $result = $service->regenerateApiKey($id);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: 'API Key regenerada');
        }
    }

    public function confirmDelete(string $id): void
    {
        if (! auth()->user()?->canDeleteSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar sitios');

            return;
        }

        $this->deletingSiteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSite(): void
    {
        if (! $this->deletingSiteId) {
            return;
        }

        if (! auth()->user()?->canDeleteSites()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para eliminar sitios');

            return;
        }

        $service = app(SiteService::class);
        $result = $service->delete($this->deletingSiteId);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: 'Sitio eliminado');
        } else {
            $this->dispatch('notify', type: 'error', message: $result['error'] ?? 'Error al eliminar');
        }

        $this->showDeleteModal = false;
        $this->deletingSiteId = null;
    }

    public function openEmbedModal(string $id): void
    {
        $service = app(SiteService::class);
        $this->embedSite = $service->find($id);

        if ($this->embedSite) {
            $this->selectedWidgetType = $this->embedSite->settings['type'] ?? 'whatsapp';
            $this->showEmbedModal = true;
        }
    }

    public function closeEmbedModal(): void
    {
        $this->showEmbedModal = false;
        $this->embedSite = null;
    }

    public function getEmbedCode(): string
    {
        if (! $this->embedSite) {
            return '';
        }

        $baseUrl = request()->getSchemeAndHttpHost();
        $settings = $this->embedSite->settings ?? [];
        $phone = $settings['phone'] ?? '';
        $position = $settings['position'] ?? 'bottom-right';
        $title = $settings['title'] ?? 'Contáctanos';
        $buttonText = $settings['button_text'] ?? 'Enviar';
        $privacyUrl = $this->embedSite->privacy_policy_url ?? '';

        // Convertir caracteres especiales a entidades numéricas para evitar problemas de encoding
        $title = mb_encode_numericentity($title, [0x80, 0xFFFF, 0, 0xFFFF], 'UTF-8');
        $buttonText = mb_encode_numericentity($buttonText, [0x80, 0xFFFF, 0, 0xFFFF], 'UTF-8');

        $attributes = [
            "data-site-id=\"{$this->embedSite->id}\"",
            "data-type=\"{$this->selectedWidgetType}\"",
        ];

        if ($phone && in_array($this->selectedWidgetType, ['whatsapp', 'phone'])) {
            $attributes[] = "data-phone=\"{$phone}\"";
        }

        // Agregar URL de política de privacidad (requerido)
        if ($privacyUrl) {
            $attributes[] = "data-privacy-url=\"{$privacyUrl}\"";
        }

        $attributes[] = "data-position=\"{$position}\"";
        $attributes[] = "data-title=\"{$title}\"";
        $attributes[] = "data-button-text=\"{$buttonText}\"";

        $attributesStr = implode("\n       ", $attributes);

        // Usar widget-serve.php para compatibilidad con WAF/ModSecurity en hosting compartido
        return "<script defer src=\"{$baseUrl}/widget-serve.php\"\n       {$attributesStr}>\n</script>";
    }

    private function resetForm(): void
    {
        $this->siteId = null;
        $this->name = '';
        $this->domain = '';
        $this->isActive = true;
        $this->privacyPolicyUrl = '';
        $this->defaultUserId = null;
        $this->widgetType = 'whatsapp';
        $this->widgetPhone = '';
        $this->widgetPosition = 'bottom-right';
        $this->widgetColor = '#3B82F6';
        $this->widgetTitle = 'Contactanos';
        $this->widgetButtonText = 'Enviar';
        $this->resetValidation();
    }

    public function render()
    {
        $service = app(SiteService::class);
        $sites = $service->getAllOrdered();

        return view('livewire.sites.index', [
            'sites' => $sites,
        ])->layout('components.layouts.app', ['title' => 'Sitios Web']);
    }
}
