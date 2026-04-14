<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set enabled_modules to null for existing clinics that have empty or restricted module lists.
     * This ensures they get access to all default modules including ENT.
     */
    public function up(): void
    {
        // Get all clinics
        $clinics = DB::table('clinics')->get();

        foreach ($clinics as $clinic) {
            $enabledModules = json_decode($clinic->enabled_modules, true);

            // If enabled_modules is an empty array, set it to null
            // This will make hasModule() return true for all modules by default
            if (is_array($enabledModules) && empty($enabledModules)) {
                DB::table('clinics')
                    ->where('id', $clinic->id)
                    ->update(['enabled_modules' => null]);
            }

            // If enabled_modules is a non-empty array but doesn't include ENT,
            // add ENT to the list (only if they have other modules enabled)
            if (is_array($enabledModules) && !empty($enabledModules) && !in_array('ent', $enabledModules)) {
                $enabledModules[] = 'ent';
                DB::table('clinics')
                    ->where('id', $clinic->id)
                    ->update(['enabled_modules' => json_encode($enabledModules)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed - we're only adding access, not removing it
    }
};
