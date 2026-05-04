<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use Illuminate\Console\Command;

class SeedHairTreatments extends Command
{
    protected $signature = 'seed:hair {tenant_id=TEN-1 : Tenant ID for the treatments}';
    protected $description = 'Seed hair aesthetic treatments';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $treatments = [
            [
                'name' => 'Hair PRP',
                'category' => 'hair',
                'default_price' => 110.00,
                'session_required' => true,
                'sessions_count' => 5,
                'description' => 'Hair regrowth',
                'is_active' => true,
            ],
            [
                'name' => 'Hair Mesotherapy',
                'category' => 'hair',
                'default_price' => 90.00,
                'session_required' => true,
                'sessions_count' => 6,
                'description' => 'Hair nourishment',
                'is_active' => true,
            ],
            [
                'name' => 'Scalp Detox',
                'category' => 'hair',
                'default_price' => 40.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Scalp cleansing',
                'is_active' => true,
            ],
            [
                'name' => 'Dandruff Treatment',
                'category' => 'hair',
                'default_price' => 35.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Anti-dandruff treatment',
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
