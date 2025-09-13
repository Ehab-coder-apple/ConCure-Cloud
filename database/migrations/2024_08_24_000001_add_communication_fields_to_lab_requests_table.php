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
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->string('lab_email')->nullable()->after('lab_name');
            $table->string('lab_phone')->nullable()->after('lab_email');
            $table->string('lab_whatsapp')->nullable()->after('lab_phone');
            $table->timestamp('sent_at')->nullable()->after('notes');
            $table->enum('communication_method', ['email', 'whatsapp', 'manual'])->nullable()->after('sent_at');
            $table->text('communication_notes')->nullable()->after('communication_method');
            $table->string('result_file_path')->nullable()->after('communication_notes');
            $table->timestamp('result_received_at')->nullable()->after('result_file_path');
            $table->unsignedBigInteger('result_received_by')->nullable()->after('result_received_at');
            
            $table->index(['status', 'sent_at']);
            $table->index(['communication_method', 'sent_at']);
            $table->foreign('result_received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropForeign(['result_received_by']);
            $table->dropIndex(['status', 'sent_at']);
            $table->dropIndex(['communication_method', 'sent_at']);
            
            $table->dropColumn([
                'lab_email',
                'lab_phone', 
                'lab_whatsapp',
                'sent_at',
                'communication_method',
                'communication_notes',
                'result_file_path',
                'result_received_at',
                'result_received_by'
            ]);
        });
    }
};
