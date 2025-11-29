<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Sites;

use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Str;
use Livewire\Component;

class SiteIndex extends Component
{
    // Form fields
    public bool $showModal = false;

    public ?string $siteId = null;

    public string $name = '';

    public string $domain = '';

    public bool $isActive = true;

    // Widget settings
    public string $widgetType = 'whatsapp';

    public string $widgetPhone = '';

    public string $widgetPosition = 'bottom-right';

    public string $widgetColor = '#3B82F6';

    public string $widgetTitle = 'Contactanos';

    public string $widgetButtonText = 'Enviar';

    // Delete modal
    public bool $showDeleteModal = false;

    public ?string $deletingSiteId = null;

    // Embed code modal
    public bool $showEmbedModal = false;

    public ?SiteModel $embedSite = null;

    public string $selectedWidgetType = 'whatsapp';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
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
        $site = SiteModel::find($id);
        if ($site) {
            $this->siteId = $id;
            $this->name = $site->name;
            $this->domain = $site->domain;
            $this->isActive = $site->is_active;

            $settings = $site->settings ?? [];
            $this->widgetType = $settings['type'] ?? 'whatsapp';
            $this->widgetPhone = $settings['phone'] ?? '';
            $this->widgetPosition = $settings['position'] ?? 'bottom-right';
            $this->widgetColor = $settings['color'] ?? '#3B82F6';
            $this->widgetTitle = $settings['title'] ?? 'Contactanos';
            $this->widgetButtonText = $settings['button_text'] ?? 'Enviar';

            $this->showModal = true;
        }
    }

    public function save(): void
    {
        $this->validate();

        $settings = [
            'type' => $this->widgetType,
            'phone' => $this->widgetPhone,
            'position' => $this->widgetPosition,
            'color' => $this->widgetColor,
            'title' => $this->widgetTitle,
            'button_text' => $this->widgetButtonText,
        ];

        if ($this->siteId) {
            SiteModel::where('id', $this->siteId)->update([
                'name' => $this->name,
                'domain' => $this->domain,
                'is_active' => $this->isActive,
                'settings' => $settings,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Sitio actualizado');
        } else {
            SiteModel::create([
                'name' => $this->name,
                'domain' => $this->domain,
                'api_key' => $this->generateApiKey(),
                'is_active' => $this->isActive,
                'settings' => $settings,
                'created_at' => now(),
            ]);
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
        $site = SiteModel::find($id);
        if ($site) {
            $site->update(['is_active' => ! $site->is_active]);
            $this->dispatch('notify', type: 'success', message: $site->is_active ? 'Sitio activado' : 'Sitio desactivado');
        }
    }

    public function regenerateApiKey(string $id): void
    {
        $site = SiteModel::find($id);
        if ($site) {
            $site->update(['api_key' => $this->generateApiKey()]);
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
        if ($this->deletingSiteId) {
            $site = SiteModel::find($this->deletingSiteId);
            if ($site) {
                $leadsCount = $site->leads()->count();
                if ($leadsCount > 0) {
                    $this->dispatch('notify', type: 'error', message: "No se puede eliminar: tiene {$leadsCount} leads asociados");
                } else {
                    $site->delete();
                    $this->dispatch('notify', type: 'success', message: 'Sitio eliminado');
                }
            }
            $this->showDeleteModal = false;
            $this->deletingSiteId = null;
        }
    }

    public function openEmbedModal(string $id): void
    {
        $this->embedSite = SiteModel::find($id);
        // Usar el tipo guardado en el sitio por defecto
        $this->selectedWidgetType = $this->embedSite->settings['type'] ?? 'whatsapp';
        $this->showEmbedModal = true;
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
        $color = $settings['color'] ?? null;
        $title = $settings['title'] ?? 'Contactanos';
        $buttonText = $settings['button_text'] ?? 'Enviar';

        $attributes = [
            "data-site-id=\"{$this->embedSite->id}\"",
            "data-type=\"{$this->selectedWidgetType}\"",
        ];

        if ($phone && in_array($this->selectedWidgetType, ['whatsapp', 'phone'])) {
            $attributes[] = "data-phone=\"{$phone}\"";
        }

        $attributes[] = "data-position=\"{$position}\"";

        // Solo incluir color si es personalizado (no los defaults por tipo)
        $defaultColors = ['#3B82F6', '#25D366', '#A855F7'];
        if ($color && ! in_array($color, $defaultColors)) {
            $attributes[] = "data-color=\"{$color}\"";
        }

        $attributes[] = "data-title=\"{$title}\"";
        $attributes[] = "data-button-text=\"{$buttonText}\"";

        $attributesStr = implode("\n       ", $attributes);

        return "<script src=\"{$baseUrl}/widget.js\"\n       {$attributesStr}>\n</script>";
    }

    private function resetForm(): void
    {
        $this->siteId = null;
        $this->name = '';
        $this->domain = '';
        $this->isActive = true;
        $this->widgetType = 'whatsapp';
        $this->widgetPhone = '';
        $this->widgetPosition = 'bottom-right';
        $this->widgetColor = '#3B82F6';
        $this->widgetTitle = 'Contactanos';
        $this->widgetButtonText = 'Enviar';
        $this->resetValidation();
    }

    private function generateApiKey(): string
    {
        return 'sk_' . Str::random(32);
    }

    public function render()
    {
        $sites = SiteModel::orderBy('created_at', 'desc')->get();

        return view('livewire.sites.index', [
            'sites' => $sites,
        ])->layout('components.layouts.app', ['title' => 'Sitios Web']);
    }
}
