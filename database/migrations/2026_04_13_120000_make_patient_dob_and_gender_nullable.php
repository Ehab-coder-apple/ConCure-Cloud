<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->change();
            $table->string('gender', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('patients')->whereNull('date_of_birth')->update([
            'date_of_birth' => '2000-01-01',
        ]);

        DB::table('patients')->whereNull('gender')->update([
            'gender' => 'other',
        ]);

        Schema::table('patients', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable(false)->change();
            $table->string('gender', 20)->nullable(false)->change();
        });
    }
};