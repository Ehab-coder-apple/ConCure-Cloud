<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_nutrition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->string('diet_type')->nullable();
            $table->text('goals')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasTable('patients')) {
            return;
        }

        DB::table('patients')
            ->select('id', 'height', 'weight', 'bmi')
            ->orderBy('id')
            ->chunkById(100, function ($patients): void {
                $timestamp = now();
                $rows = [];

                foreach ($patients as $patient) {
                    if ($patient->height === null && $patient->weight === null && $patient->bmi === null) {
                        continue;
                    }

                    $rows[] = [
                        'patient_id' => $patient->id,
                        'height' => $patient->height,
                        'weight' => $patient->weight,
                        'bmi' => $patient->bmi,
                        'diet_type' => null,
                        'goals' => null,
                        'notes' => null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                if ($rows !== []) {
                    DB::table('patient_nutrition')->upsert(
                        $rows,
                        ['patient_id'],
                        ['height', 'weight', 'bmi', 'updated_at']
                    );
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_nutrition');
    }
};