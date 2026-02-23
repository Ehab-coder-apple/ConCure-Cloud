<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
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
                'dental_dept',
                'dental_technician',
                'cad_cam_designer'
            ) NOT NULL");
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        // SQLite: recreate users table to update the CHECK constraint for enum.
        // Use an explicit column list copy (instead of SELECT *) so this remains robust
        // even if previous SQLite schema versions are missing some columns.

        try {
            DB::statement('DROP INDEX IF EXISTS users_new_role_is_active_index');
            DB::statement('DROP INDEX IF EXISTS users_new_clinic_id_index');
            DB::statement('DROP INDEX IF EXISTS users_new_username_unique');
            DB::statement('DROP INDEX IF EXISTS users_new_email_unique');
        } catch (\Exception $e) {
            // ignore
        }

        Schema::dropIfExists('users_new');

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
            $table->unsignedTinyInteger('doctor_name_font_size')->default(12);

            $table->text('specialization')->nullable();
            $table->unsignedTinyInteger('specialization_font_size')->default(10);
            $table->text('medical_degrees')->nullable();
            $table->unsignedTinyInteger('medical_degrees_font_size')->default(9);
            $table->text('professional_credentials')->nullable();
            $table->unsignedTinyInteger('professional_credentials_font_size')->default(9);
            $table->string('scientific_degree')->nullable();
            $table->string('educational_institution')->nullable();

            $table->enum('role', [
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
                'dental_dept',
                'dental_technician',
                'cad_cam_designer',
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

        DB::statement('CREATE UNIQUE INDEX users_new_username_unique ON users_new (username)');
        DB::statement('CREATE UNIQUE INDEX users_new_email_unique ON users_new (email)');
        DB::statement('CREATE INDEX users_new_role_is_active_index ON users_new (role, is_active)');
        DB::statement('CREATE INDEX users_new_clinic_id_index ON users_new (clinic_id)');

        $targetColumns = [
            'id',
            'username',
            'email',
            'email_verified_at',
            'password',
            'first_name',
            'last_name',
            'phone',
            'title_prefix',
            'doctor_name_font_size',
            'specialization',
            'specialization_font_size',
            'medical_degrees',
            'medical_degrees_font_size',
            'professional_credentials',
            'professional_credentials_font_size',
            'scientific_degree',
            'educational_institution',
            'role',
            'is_active',
            'activation_code',
            'activated_at',
            'expires_at',
            'last_login_at',
            'language',
            'permissions',
            'can_manage_form_templates',
            'can_assign_forms',
            'can_fill_forms',
            'metadata',
            'clinic_id',
            'created_by',
            'remember_token',
            'created_at',
            'updated_at',
        ];

        $existingColumns = Schema::hasTable('users') ? Schema::getColumnListing('users') : [];
        $existingColumns = array_map('strtolower', $existingColumns);

        $selectExpr = [];
        foreach ($targetColumns as $col) {
            $has = in_array(strtolower($col), $existingColumns, true);

            if ($has) {
                $selectExpr[] = match ($col) {
                    'is_active' => 'COALESCE(is_active, 1) AS is_active',
                    'language' => "COALESCE(language, 'en') AS language",
                    'doctor_name_font_size' => 'COALESCE(doctor_name_font_size, 12) AS doctor_name_font_size',
                    'specialization_font_size' => 'COALESCE(specialization_font_size, 10) AS specialization_font_size',
                    'medical_degrees_font_size' => 'COALESCE(medical_degrees_font_size, 9) AS medical_degrees_font_size',
                    'professional_credentials_font_size' => 'COALESCE(professional_credentials_font_size, 9) AS professional_credentials_font_size',
                    'can_manage_form_templates' => 'COALESCE(can_manage_form_templates, 0) AS can_manage_form_templates',
                    'can_assign_forms' => 'COALESCE(can_assign_forms, 0) AS can_assign_forms',
                    'can_fill_forms' => 'COALESCE(can_fill_forms, 0) AS can_fill_forms',
                    'created_at' => 'COALESCE(created_at, CURRENT_TIMESTAMP) AS created_at',
                    'updated_at' => 'COALESCE(updated_at, CURRENT_TIMESTAMP) AS updated_at',
                    default => "$col AS $col",
                };
                continue;
            }

            $selectExpr[] = match ($col) {
                'is_active' => '1 AS is_active',
                'language' => "'en' AS language",
                'doctor_name_font_size' => '12 AS doctor_name_font_size',
                'specialization_font_size' => '10 AS specialization_font_size',
                'medical_degrees_font_size' => '9 AS medical_degrees_font_size',
                'professional_credentials_font_size' => '9 AS professional_credentials_font_size',
                'can_manage_form_templates' => '0 AS can_manage_form_templates',
                'can_assign_forms' => '0 AS can_assign_forms',
                'can_fill_forms' => '0 AS can_fill_forms',
                'created_at' => 'CURRENT_TIMESTAMP AS created_at',
                'updated_at' => 'CURRENT_TIMESTAMP AS updated_at',
                'role' => "'patient' AS role",
                default => "NULL AS $col",
            };
        }

        DB::statement(
            'INSERT INTO users_new (' . implode(', ', $targetColumns) . ') ' .
            'SELECT ' . implode(', ', $selectExpr) . ' FROM users'
        );

        DB::statement('PRAGMA foreign_keys = OFF');
        Schema::drop('users');
        Schema::rename('users_new', 'users');
        DB::statement('PRAGMA foreign_keys = ON');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
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
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            throw new \Exception('Rollback not supported for SQLite. Please restore from backup if needed.');
        }
    }
};
