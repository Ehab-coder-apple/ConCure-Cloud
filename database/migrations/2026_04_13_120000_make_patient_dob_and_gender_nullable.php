<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE patients MODIFY date_of_birth DATE NULL');
        DB::statement("ALTER TABLE patients MODIFY gender ENUM('male', 'female', 'other') NULL");
    }

    public function down(): void
    {
        DB::table('patients')->whereNull('date_of_birth')->update([
            'date_of_birth' => '2000-01-01',
        ]);

        DB::table('patients')->whereNull('gender')->update([
            'gender' => 'other',
        ]);

        DB::statement('ALTER TABLE patients MODIFY date_of_birth DATE NOT NULL');
        DB::statement("ALTER TABLE patients MODIFY gender ENUM('male', 'female', 'other') NOT NULL");
    }
};