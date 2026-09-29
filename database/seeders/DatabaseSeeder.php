<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SecurityAccountSeeder::class);
        $this->call(ReferenceDataSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(KaryawanSeeder::class);
    }
}
