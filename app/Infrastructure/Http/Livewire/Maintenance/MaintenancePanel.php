<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Livewire\Maintenance;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class MaintenancePanel extends Component
{
    public string $output = '';

    public bool $isRunning = false;

    public ?string $lastCommand = null;

    protected array $allowedCommands = [
        'cache:clear' => ['name' => 'Limpiar Caché App', 'danger' => false],
        'config:clear' => ['name' => 'Limpiar Caché Config', 'danger' => false],
        'route:clear' => ['name' => 'Limpiar Caché Rutas', 'danger' => false],
        'view:clear' => ['name' => 'Limpiar Caché Vistas', 'danger' => false],
        'optimize:clear' => ['name' => 'Limpiar Todo', 'danger' => false],
        'migrate' => ['name' => 'Ejecutar Migraciones', 'danger' => true, 'options' => ['--force' => true]],
        'db:seed' => ['name' => 'Ejecutar Seeders', 'danger' => true, 'options' => ['--force' => true]],
        'migrate:rollback' => ['name' => 'Rollback Migración', 'danger' => true, 'options' => ['--force' => true]],
        'optimize' => ['name' => 'Optimizar App', 'danger' => false],
        'storage:link' => ['name' => 'Crear Storage Link', 'danger' => false],
        'migrate:status' => ['name' => 'Estado Migraciones', 'danger' => false],
    ];

    public function runCommand(string $command): void
    {
        if (! array_key_exists($command, $this->allowedCommands)) {
            $this->addOutput("Error: Comando no permitido: {$command}", 'error');

            return;
        }

        $this->isRunning = true;
        $this->lastCommand = $command;

        $commandInfo = $this->allowedCommands[$command];
        $options = $commandInfo['options'] ?? [];

        $this->addOutput("$ php artisan {$command}" . ($options ? ' ' . implode(' ', array_keys($options)) : ''), 'command');

        try {
            $exitCode = Artisan::call($command, $options);
            $commandOutput = Artisan::output();

            if ($commandOutput) {
                $this->addOutput($commandOutput, $exitCode === 0 ? 'success' : 'warning');
            }

            if ($exitCode === 0) {
                $this->addOutput("Comando completado exitosamente.", 'success');
                $this->dispatch('notify', type: 'success', message: $commandInfo['name'] . ' ejecutado');
            } else {
                $this->addOutput("Comando terminó con código: {$exitCode}", 'warning');
                $this->dispatch('notify', type: 'warning', message: 'Comando terminó con advertencias');
            }
        } catch (\Exception $e) {
            $this->addOutput("Error: " . $e->getMessage(), 'error');
            $this->dispatch('notify', type: 'error', message: 'Error al ejecutar comando');
        }

        $this->isRunning = false;
    }

    public function clearAllCache(): void
    {
        $this->addOutput("=== Limpiando todas las cachés ===", 'info');

        $commands = ['cache:clear', 'config:clear', 'route:clear', 'view:clear'];

        foreach ($commands as $command) {
            $this->runCommand($command);
        }

        $this->addOutput("=== Limpieza completada ===", 'info');
    }

    public function clearOutput(): void
    {
        $this->output = '';
        $this->lastCommand = null;
    }

    private function addOutput(string $text, string $type = 'default'): void
    {
        $timestamp = now()->format('H:i:s');
        $prefix = match ($type) {
            'command' => '<span class="text-cyan-400">',
            'success' => '<span class="text-green-400">',
            'error' => '<span class="text-red-400">',
            'warning' => '<span class="text-yellow-400">',
            'info' => '<span class="text-blue-400">',
            default => '<span class="text-gray-300">',
        };

        $this->output .= "<div class=\"mb-1\">[{$timestamp}] {$prefix}" . e($text) . "</span></div>";
    }

    public function render()
    {
        return view('livewire.maintenance.panel')
            ->layout('components.layouts.app', ['title' => 'Mantenimiento']);
    }
}
