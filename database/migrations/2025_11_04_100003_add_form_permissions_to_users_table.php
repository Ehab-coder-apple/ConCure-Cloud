<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Basic booleans for Forms permissions (co-exist with JSON permissions field)
            $table->boolean('can_manage_form_templates')->default(false)->after('permissions');
            $table->boolean('can_assign_forms')->default(false)->after('can_manage_form_templates');
            $table->boolean('can_fill_forms')->default(false)->after('can_assign_forms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_manage_form_templates', 'can_assign_forms', 'can_fill_forms']);
        });
    }
};

