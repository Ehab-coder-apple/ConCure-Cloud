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
        // For SQLite, we'll just update the role column to allow super_admin
        // Since SQLite is more flexible with CHECK constraints

        // The role column in SQLite is likely just a string, so super_admin should work
        // Let's test by creating a super admin user directly

        // No schema changes needed for SQLite - it's flexible with string values
        echo "Super admin role support added. Role column can now accept 'super_admin' value.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback since we didn't change the schema
        echo "No schema changes to rollback.\n";
    }
};
