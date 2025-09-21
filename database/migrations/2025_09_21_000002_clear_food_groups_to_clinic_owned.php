<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Detach foods from groups to satisfy FK restrictions
        DB::statement('UPDATE foods SET food_group_id = NULL');

        // 2) Remove all existing food groups so clinics can create their own lists
        DB::statement('DELETE FROM food_groups');
    }

    public function down(): void
    {
        // Irreversible data cleanup. No-op.
    }
};

