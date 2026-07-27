<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Merge all Antibiotic variants into "Antibiotics"
        DB::table('pediatric_drugs')
            ->whereRaw('LOWER(category) LIKE ?', ['antibiotic%'])
            ->update(['category' => 'Antibiotics']);

        // 2. Merge "Antihistamine" (singular) into "Antihistamines" (plural)
        DB::table('pediatric_drugs')
            ->whereRaw('LOWER(category) = ?', ['antihistamine'])
            ->update(['category' => 'Antihistamines']);

        // 2b. Merge "Steroid" (singular) into "Steroids" (plural)
        DB::table('pediatric_drugs')
            ->whereRaw('LOWER(category) = ?', ['steroid'])
            ->update(['category' => 'Steroids']);

        // 3. Capitalize the first letter of all category names (driver-agnostic via PHP)
        $rows = DB::table('pediatric_drugs')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('id', 'category')
            ->get();

        foreach ($rows as $row) {
            $normalized = ucfirst($row->category);
            if ($normalized !== $row->category) {
                DB::table('pediatric_drugs')
                    ->where('id', $row->id)
                    ->update(['category' => $normalized]);
            }
        }
    }

    public function down(): void
    {
        // Data normalization — not reversible in a meaningful way
    }
};

