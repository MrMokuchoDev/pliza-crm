<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalePhaseSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            [
                'name' => 'Sin contactar',
                'order' => 1,
                'color' => '#6B7280',
                'is_closed' => false,
                'is_won' => false,
                'is_default' => true,
            ],
            [
                'name' => 'Calificado',
                'order' => 2,
                'color' => '#3B82F6',
                'is_closed' => false,
                'is_won' => false,
                'is_default' => false,
            ],
            [
                'name' => 'Negociación',
                'order' => 3,
                'color' => '#F59E0B',
                'is_closed' => false,
                'is_won' => false,
                'is_default' => false,
            ],
            [
                'name' => 'Cerrado Ganado',
                'order' => 4,
                'color' => '#10B981',
                'is_closed' => true,
                'is_won' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Cerrado Perdido',
                'order' => 5,
                'color' => '#EF4444',
                'is_closed' => true,
                'is_won' => false,
                'is_default' => false,
            ],
        ];

        foreach ($phases as $phase) {
            SalePhaseModel::updateOrCreate(
                ['name' => $phase['name']],
                array_merge(['id' => Str::uuid()->toString()], $phase)
            );
        }
    }
}
