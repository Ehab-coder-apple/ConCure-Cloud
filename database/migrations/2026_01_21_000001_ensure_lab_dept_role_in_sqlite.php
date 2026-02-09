<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table to update the CHECK constraint
        if (DB::getDriverName() === 'sqlite') {
            // Drop existing indexes first
            try {
                DB::statement('DROP INDEX IF EXISTS users_new_role_is_active_index');
                DB::statement('DROP INDEX IF EXISTS users_new_clinic_id_index');
                DB::statement('DROP INDEX IF EXISTS users_new_username_unique');
                DB::statement('DROP INDEX IF EXISTS users_new_email_unique');
            } catch (\Exception $e) {
                // Indexes might not exist, continue
            }

            // Drop users_new table if it exists from a previous failed migration
            Schema::dropIfExists('users_new');

            // Create new table with updated role enum
            Schema::create('users_new', function (Blueprint $table) {
                $table->id();
                $table->string('username');
                $table->string('email');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone')->nullable();
                $table->string('title_prefix')->nullable();
                // Updated to include all roles including lab_dept and radiology_dept
                $table->enum('role', [
                    'super_admin', 'master_admin', 'admin', 'doctor', 'assistant',
                    'nurse', 'accountant', 'patient', 'nutritionist', 'pharmacist',
                    'lab_dept', 'radiology_dept'
                ]);
                $table->boolean('is_active')->default(true);
                $table->string('activation_code')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->string('language', 2)->default('en');
                $table->json('permissions')->nullable();
                $table->boolean('can_manage_form_templates')->default(false);
                $table->boolean('can_assign_forms')->default(false);
                $table->boolean('can_fill_forms')->default(false);
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });

            // Add indexes after table creation
            DB::statement('CREATE UNIQUE INDEX users_new_username_unique ON users_new (username)');
            DB::statement('CREATE UNIQUE INDEX users_new_email_unique ON users_new (email)');
            DB::statement('CREATE INDEX users_new_role_is_active_index ON users_new (role, is_active)');
            DB::statement('CREATE INDEX users_new_clinic_id_index ON users_new (clinic_id)');

            // Copy all data from old table with COALESCE for nullable fields
            DB::statement("
                INSERT INTO users_new (
                    id, username, email, email_verified_at, password,
                    first_name, last_name, phone, title_prefix, role,
                    is_active, activation_code, activated_at, expires_at, last_login_at,
                    language, permissions, can_manage_form_templates, can_assign_forms, can_fill_forms,
                    metadata, clinic_id, created_by, remember_token, created_at, updated_at
                )
                SELECT
                    id, username, email, email_verified_at, password,
                    first_name, last_name, phone, title_prefix, role,
                    COALESCE(is_active, 1), activation_code, activated_at, expires_at, last_login_at,
                    COALESCE(language, 'en'), permissions,
                    COALESCE(can_manage_form_templates, 0),
                    COALESCE(can_assign_forms, 0),
                    COALESCE(can_fill_forms, 0),
                    metadata, clinic_id, created_by, remember_token, created_at, updated_at
                FROM users
            ");

            // Disable foreign key checks temporarily
            DB::statement('PRAGMA foreign_keys = OFF');

            // Drop old table and rename new one
            Schema::drop('users');
            Schema::rename('users_new', 'users');

            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys = ON');

            // Recreate foreign keys
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            });
        }
        // For MySQL, the role was already added in previous migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback not needed as this is just ensuring the role exists
    }
};

