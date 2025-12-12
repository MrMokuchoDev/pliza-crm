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

    public function mount(): void
    {
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
            'defaultUserId' => 'nullable|exists:users,uuid',
            'widgetType' => 'required|in:whatsapp,phone,contact_form',
            'widgetPhone' => 'nullable|string|max:50',
            'widgetPosition' => 'required|in:bottom-right,bottom-left,top-right,top-left',
            'widgetColor' => 'required|string|max:20',
            'widgetTitle' => 'nullable|string|max:255',
            'widgetButtonText' => 'nullable|string|max:100',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $service = app(SiteService::class);
        $site = $service->find($id);

        if ($site) {
            $this->siteId = $id;
            $this->name = $site->name;
            $this->domain = $site->domain;
            $this->isActive = $site->is_active;
            $this->defaultUserId = $site->default_user_id;

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
            defaultUserId: $this->defaultUserId ?: null,
        );

        if ($this->siteId) {
            $service->update($this->siteId, $data);
            $this->dispatch('notify', type: 'success', message: 'Sitio actualizado');
        } else {
            $service->create($data);
            $this->dispatch('notify', type: 'success', message: 'Sitio creado');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        $service = app(SiteService::class);
        $result = $service->toggleActive($id);

        if ($result['success']) {
            $message = $result['is_active'] ? 'Sitio activado' : 'Sitio desactivado';
            $this->dispatch('notify', type: 'success', message: $message);
        }
    }

    public function regenerateApiKey(string $id): void
    {
        $service = app(SiteService::class);
        $result = $service->regenerateApiKey($id);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: 'API Key regenerada');
        }
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingSiteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSite(): void
    {
        if (! $this->deletingSiteId) {
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
