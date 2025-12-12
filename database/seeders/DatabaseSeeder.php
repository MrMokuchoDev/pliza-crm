<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear roles y permisos
        $this->call([
            RolePermissionSeeder::class,
            SalePhaseSeeder::class,
            LeadSeeder::class,
            DealSeeder::class,
        ]);

        // Obtener el rol admin
        $adminRole = RoleModel::where('name', 'admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@minicrm.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => $adminRole?->id,
                'is_active' => true,
            ]
        );
    }
}
