<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'demo'],
            [
                'name'  => 'Demo Company',
                'email' => 'demo@example.com',
            ]
        );

        $tenant->domains()->firstOrCreate([
            'domain' => env('DEMO_DOMAIN', 'demo.localhost'),
        ]);
    }
}