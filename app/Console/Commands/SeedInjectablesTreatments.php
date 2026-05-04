<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use Illuminate\Console\Command;

class SeedInjectablesTreatments extends Command
{
    protected $signature = 'seed:injectables {tenant_id=TEN-1 : Tenant ID for the treatments}';
    protected $description = 'Seed injectable aesthetic treatments';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $treatments = [
            [
                'name' => 'Botox - Forehead',
                'category' => 'injectables',
                'default_price' => 10.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Reduce forehead wrinkles',
                'is_active' => true,
            ],
            [
                'name' => "Botox - Crow's Feet",
                'category' => 'injectables',
                'default_price' => 10.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Reduce eye wrinkles',
                'is_active' => true,
            ],
            [
                'name' => 'Dermal Filler - Lips',
                'category' => 'injectables',
                'default_price' => 250.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Lip enhancement',
                'is_active' => true,
            ],
            [
                'name' => 'Dermal Filler - Jawline',
                'category' => 'injectables',
                'default_price' => 300.00,
                'session_required' => false,
                'sessions_count' => null,
                'description' => 'Jawline contouring',
                'is_active' => true,
            ],
            [
                'name' => 'PRP - Face',
                'category' => 'injectables',
                'default_price' => 100.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Skin rejuvenation',
                'is_active' => true,
            ],
            [
                'name' => 'PRP - Hair',
                'category' => 'injectables',
                'default_price' => 120.00,
                'session_required' => true,
                'sessions_count' => 4,
                'description' => 'Hair regrowth treatment',
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
