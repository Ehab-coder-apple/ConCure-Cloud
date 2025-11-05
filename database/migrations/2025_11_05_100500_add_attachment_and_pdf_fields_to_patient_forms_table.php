<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            // Attachment fields
            $table->string('attachment_path')->nullable()->after('notes');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime')->nullable()->after('attachment_name');
            $table->bigInteger('attachment_size')->nullable()->after('attachment_mime');

            // Stored PDF snapshot fields
            $table->string('pdf_path')->nullable()->after('attachment_size');
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_path',
                'attachment_name',
                'attachment_mime',
                'attachment_size',
                'pdf_path',
                'pdf_generated_at',
            ]);
        });
    }
};

