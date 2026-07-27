<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use Illuminate\Console\Command;

class SeedSkincareTreatments extends Command
{
    protected $signature = 'seed:skincare {tenant_id=TEN-1 : Tenant ID for the treatments}';
    protected $description = 'Seed skincare aesthetic treatments';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $treatments = [
            [
                'name' => 'HydraFacial',
                'category' => 'skincare',
                'default_price' => 60.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Deep hydration facial',
                'is_active' => true,
            ],
            [
                'name' => 'Chemical Peel - Light',
                'category' => 'skincare',
                'default_price' => 50.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Mild exfoliation',
                'is_active' => true,
            ],
            [
                'name' => 'Chemical Peel - Medium',
                'category' => 'skincare',
                'default_price' => 80.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Moderate skin peeling',
                'is_active' => true,
            ],
            [
                'name' => 'Microneedling',
                'category' => 'skincare',
                'default_price' => 90.00,
                'session_required' => true,
                'sessions_count' => 5,
                'description' => 'Collagen stimulation',
                'is_active' => true,
            ],
            [
                'name' => 'Skin Booster Injection',
                'category' => 'skincare',
                'default_price' => 150.00,
                'session_required' => true,
                'sessions_count' => 3,
                'description' => 'Hydration injection',
                'is_active' => true,
            ],
            [
                'name' => 'Acne Facial',
                'category' => 'skincare',
                'default_price' => 45.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Acne treatment facial',
                'is_active' => true,
            ],
            [
                'name' => 'Whitening Facial',
                'category' => 'skincare',
                'default_price' => 55.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Brightening treatment',
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
