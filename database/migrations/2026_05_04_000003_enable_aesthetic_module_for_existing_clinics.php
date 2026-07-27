<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $clinics = DB::table('clinics')->get();

        foreach ($clinics as $clinic) {
            $enabled = json_decode($clinic->enabled_modules, true);

            if (is_array($enabled) && !empty($enabled) && !in_array('aesthetic', $enabled)) {
                $enabled[] = 'aesthetic';
                DB::table('clinics')
                    ->where('id', $clinic->id)
                    ->update(['enabled_modules' => json_encode($enabled)]);
            }

            // If null, all modules are enabled by default, so aesthetic is already available
        }
    }

    public function down(): void
    {
        // No reversal needed
    }
};
