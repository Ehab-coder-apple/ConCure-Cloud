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
        // For SQLite compatibility, we'll recreate the table with the new enum values
        if (DB::getDriverName() === 'sqlite') {
            // SQLite approach: Create new table and migrate data
            Schema::create('users_new', function (Blueprint $table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone')->nullable();
                $table->string('title_prefix')->nullable();
                $table->enum('role', ['super_admin', 'admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient', 'nutritionist']);
                $table->boolean('is_active')->default(true);
                $table->string('activation_code')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->string('language', 2)->default('en');
                $table->json('permissions')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->index(['role', 'is_active']);
                $table->index('clinic_id');
            });

            // Copy data from old table with safe defaults for NOT NULL columns
            DB::statement("INSERT INTO users_new (
                id, username, email, email_verified_at, password,
                first_name, last_name, phone, title_prefix,
                role, is_active, activation_code, activated_at, last_login_at,
                language, permissions, metadata, clinic_id, created_by,
                remember_token, created_at, updated_at
            )
            SELECT
                id, username, email, email_verified_at, password,
                first_name, last_name, phone, title_prefix,
                COALESCE(role, 'admin') as role,
                COALESCE(is_active, 1) as is_active,
                activation_code, activated_at, last_login_at,
                COALESCE(language, 'en') as language,
                permissions, metadata, clinic_id, created_by,
                remember_token, created_at, updated_at
            FROM users");

            // Drop old table and rename new one
            Schema::drop('users');
            Schema::rename('users_new', 'users');

            // Recreate foreign keys
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            });
        } else {
            // MySQL approach
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient', 'nutritionist') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we'd need to recreate the table again - complex rollback
            // For now, just log that rollback is not supported for SQLite
            throw new \Exception('Rollback not supported for SQLite. Please restore from backup if needed.');
        } else {
            // MySQL rollback
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient', 'nutritionist') NOT NULL");
        }
    }
};
