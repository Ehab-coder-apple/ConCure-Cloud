<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_images', function (Blueprint $table) {
            // JSON column for image-level condition tags (e.g., ["Diabetes","Knee","MRI"]).
            // Nullable to keep backward compatibility with existing rows.
            if (!Schema::hasColumn('patient_images', 'condition_tags')) {
                $table->json('condition_tags')->nullable()->after('caption');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_images', function (Blueprint $table) {
            if (Schema::hasColumn('patient_images', 'condition_tags')) {
                $table->dropColumn('condition_tags');
            }
        });
    }
};

