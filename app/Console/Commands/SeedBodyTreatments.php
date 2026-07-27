<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use Illuminate\Console\Command;

class SeedBodyTreatments extends Command
{
    protected $signature = 'seed:body {tenant_id=TEN-1 : Tenant ID for the treatments}';
    protected $description = 'Seed body aesthetic treatments';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $treatments = [
            [
                'name' => 'Fat Dissolving Injection',
                'category' => 'body',
                'default_price' => 120.00,
                'session_required' => true,
                'sessions_count' => 3,
                'description' => 'Fat reduction',
                'is_active' => true,
            ],
            [
                'name' => 'Body Contouring RF',
                'category' => 'body',
                'default_price' => 70.00,
                'session_required' => true,
                'sessions_count' => 8,
                'description' => 'Skin tightening',
                'is_active' => true,
            ],
            [
                'name' => 'Cavitation Slimming',
                'category' => 'body',
                'default_price' => 65.00,
                'session_required' => true,
                'sessions_count' => 8,
                'description' => 'Fat reduction',
                'is_active' => true,
            ],
            [
                'name' => 'Cellulite Treatment',
                'category' => 'body',
                'default_price' => 75.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Reduce cellulite',
                'is_active' => true,
            ],
            [
                'name' => 'Stretch Marks Treatment',
                'category' => 'body',
                'default_price' => 90.00,
                'session_required' => true,
                'sessions_count' => 5,
                'description' => 'Reduce stretch marks',
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
