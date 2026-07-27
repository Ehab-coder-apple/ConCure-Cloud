<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // MySQL: Modify ENUM to include dental_dept
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'super_admin',
                'master_admin',
                'admin',
                'doctor',
                'assistant',
                'nurse',
                'accountant',
                'patient',
                'nutritionist',
                'pharmacist',
                'lab_dept',
                'radiology_dept',
                'dental_dept'
            ) NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite: Recreate table with new enum value
            
            // Drop users_new table if it exists from a previous failed migration
            Schema::dropIfExists('users_new');

            // Also drop any leftover index from a previous partial run (SQLite keeps index names globally)
            try {
                DB::statement('DROP INDEX IF EXISTS users_new_role_is_active_index');
                DB::statement('DROP INDEX IF EXISTS users_new_clinic_id_index');
            } catch (\Exception $e) {
                // Ignore — index may not exist
            }

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
                // Updated to include dental_dept
                $table->enum('role', [
                    'super_admin', 'master_admin', 'admin', 'doctor', 'assistant',
                    'nurse', 'accountant', 'patient', 'nutritionist', 'pharmacist',
                    'lab_dept', 'radiology_dept', 'dental_dept'
                ]);
                $table->boolean('is_active')->default(true);
                $table->string('activation_code')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->string('language', 2)->default('en');
                $table->json('permissions')->nullable();
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->boolean('can_assign_forms')->default(false);
                $table->boolean('can_fill_forms')->default(false);
                $table->boolean('can_manage_form_templates')->default(false);
                $table->rememberToken();
                $table->timestamps();

                $table->index(['role', 'is_active']);
                $table->index('clinic_id');
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO users_new SELECT * FROM users');

            // Drop old table
            Schema::drop('users');

            // Rename new table to users
            Schema::rename('users_new', 'users');

            // Recreate foreign keys
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Remove dental_dept from enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'super_admin',
                'master_admin',
                'admin',
                'doctor',
                'assistant',
                'nurse',
                'accountant',
                'patient',
                'nutritionist',
                'pharmacist',
                'lab_dept',
                'radiology_dept'
            ) NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // For SQLite, rollback is complex - would need to recreate table again
            throw new \Exception('Rollback not supported for SQLite. Please restore from backup if needed.');
        }
    }
};

