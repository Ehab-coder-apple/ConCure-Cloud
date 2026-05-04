<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all built-in treatments (TEN-1)
        $builtIns = DB::table('aesthetic_treatments')
            ->where('tenant_id', 'TEN-1')
            ->get();

        if ($builtIns->isEmpty()) {
            return;
        }

        // Get all unique tenant IDs from clinics (excluding TEN-1)
        $tenantIds = DB::table('clinics')
            ->whereNotNull('tenant_id')
            ->where('tenant_id', '!=', 'TEN-1')
            ->distinct()
            ->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            foreach ($builtIns as $treatment) {
                $exists = DB::table('aesthetic_treatments')
                    ->where('tenant_id', $tenantId)
                    ->where('name', $treatment->name)
                    ->where('category', $treatment->category)
                    ->exists();

                if (!$exists) {
                    DB::table('aesthetic_treatments')->insert([
                        'tenant_id' => $tenantId,
                        'name' => $treatment->name,
                        'category' => $treatment->category,
                        'default_price' => $treatment->default_price,
                        'session_required' => $treatment->session_required,
                        'sessions_count' => $treatment->sessions_count,
                        'description' => $treatment->description,
                        'is_active' => $treatment->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No reversal — cloned treatments belong to clinics now
    }
};
