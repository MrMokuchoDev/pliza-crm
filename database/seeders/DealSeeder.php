<?php

namespace Database\Seeders;

use App\Application\Deal\DTOs\DealData;
use App\Application\Deal\Services\DealService;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    public function __construct(
        private readonly DealService $dealService,
    ) {}

    public function run(): void
    {
        $phases = SalePhaseModel::orderBy('order')->get();

        if ($phases->isEmpty()) {
            $this->command->warn('No hay fases de venta. Ejecuta SalePhaseSeeder primero.');

            return;
        }

        $leads = LeadModel::all();

        if ($leads->isEmpty()) {
            $this->command->warn('No hay leads. Ejecuta LeadSeeder primero.');

            return;
        }

        $dealNames = [
            'Implementación CRM',
            'Licencias anuales',
            'Consultoría inicial',
            'Soporte premium',
            'Migración de datos',
            'Capacitación equipo',
            'Integración API',
            'Plan empresarial',
            'Módulo de reportes',
            'Automatización',
        ];

        $createdCount = 0;

        foreach ($leads as $index => $lead) {
            // 70% de leads tienen deal
            if (rand(1, 10) > 7) {
                continue;
            }

            // Verificar si ya tiene un deal
            if ($lead->deals()->exists()) {
                continue;
            }

            // Seleccionar fase distribuida
            $phaseIndex = $index % $phases->count();
            $phase = $phases[$phaseIndex];

            // Valor solo si es fase ganada
            $value = $phase->is_won ? rand(1000, 50000) * 100 : null;

            $dealName = $dealNames[array_rand($dealNames)];

            $dealData = new DealData(
                leadId: $lead->id,
                name: $dealName,
                value: $value,
                salePhaseId: $phase->id,
            );

            $this->dealService->create($dealData);
            $createdCount++;
        }

        $this->command->info("{$createdCount} deals creados para leads.");
    }
}
