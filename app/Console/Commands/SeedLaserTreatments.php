<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use Illuminate\Console\Command;

class SeedLaserTreatments extends Command
{
    protected $signature = 'seed:laser {tenant_id=TEN-1 : Tenant ID for the treatments}';
    protected $description = 'Seed laser aesthetic treatments';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $treatments = [
            [
                'name' => 'Laser Hair Removal - Face',
                'category' => 'laser',
                'default_price' => 40.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Facial hair removal',
                'is_active' => true,
            ],
            [
                'name' => 'Laser Hair Removal - Underarm',
                'category' => 'laser',
                'default_price' => 30.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Underarm hair removal',
                'is_active' => true,
            ],
            [
                'name' => 'Laser Hair Removal - Full Body',
                'category' => 'laser',
                'default_price' => 150.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Full body hair removal',
                'is_active' => true,
            ],
            [
                'name' => 'Fractional CO2 Laser',
                'category' => 'laser',
                'default_price' => 200.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Scar and skin resurfacing',
                'is_active' => true,
            ],
            [
                'name' => 'IPL - Skin Rejuvenation',
                'category' => 'laser',
                'default_price' => 90.00,
                'session_required' => true,
                'sessions_count' => 5,
                'description' => 'Pigmentation treatment',
                'is_active' => true,
            ],
            [
                'name' => 'Laser Acne Treatment',
                'category' => 'laser',
                'default_price' => 80.00,
                'session_required' => true,
                'sessions_count' => 5,
                'description' => 'Acne control',
                'is_active' => true,
            ],
            [
                'name' => 'Laser Tattoo Removal',
                'category' => 'laser',
                'default_price' => 100.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Tattoo removal',
                'is_active' => true,
            ],
            [
                'name' => 'Carbon Laser Peel',
                'category' => 'laser',
                'default_price' => 70.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Deep skin cleansing',
                'is_active' => true,
            ],
        ];

        foreach ($treatments as $data) {
            $exists = AestheticTreatment::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('name', $data['name'])
                ->exists();

            if ($exists) {
                $this->warn("Skipping: {$data['name']} already exists.");
                continue;
            }

            AestheticTreatment::withoutGlobalScope('tenant')->create(
                array_merge($data, ['tenant_id' => $tenantId])
            );

            $this->info("Created: {$data['name']}");
        }

        $this->info('Done.');
        return 0;
    }
}
