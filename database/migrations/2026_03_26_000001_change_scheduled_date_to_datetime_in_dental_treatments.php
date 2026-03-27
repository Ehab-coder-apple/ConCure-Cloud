<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->dateTime('scheduled_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->change();
        });
    }
};

