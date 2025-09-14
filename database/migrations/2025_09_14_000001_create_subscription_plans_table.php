<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed a few default plans for immediate use
        DB::table('subscription_plans')->insert([
            [
                'name' => 'Basic',
                'monthly_price' => 29.00,
                'yearly_price' => 290.00,
                'max_users' => 5,
                'features' => json_encode(['patients:unlimited','prescriptions','appointments']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professional',
                'monthly_price' => 59.00,
                'yearly_price' => 590.00,
                'max_users' => 20,
                'features' => json_encode(['patients:unlimited','prescriptions','appointments','reports']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise',
                'monthly_price' => 129.00,
                'yearly_price' => 1290.00,
                'max_users' => null,
                'features' => json_encode(['all_features']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

