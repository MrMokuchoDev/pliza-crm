<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SalePhaseSeeder::class,
            LeadSeeder::class,
            DealSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@minicrm.test'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
    }
}
