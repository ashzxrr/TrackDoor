<?php

namespace Database\Seeders;

use App\Models\SecurityAccount;
use Illuminate\Database\Seeder;

class SecurityAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SecurityAccount::updateOrCreate(
            ['username' => 'security'],
            ['password' => 'trackdoor123'],
        );
    }
}
