<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // jpg, png, pdf, dcm
            
            // ✅ Category
            $table->enum('category', [
                'xray',
                'photo',
                'consent_form',
                'treatment_plan',
                'lab_report',
                'prescription',
                'referral',
                'other'
            ])->default('other');
            
            // ✅ AI Scan fields
            $table->boolean('is_xray')->default(false);
            $table->enum('scan_status', [
                'not_applicable',
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('not_applicable');
            $table->json('scan_results')->nullable();
            $table->json('detected_conditions')->nullable();
            $table->decimal('scan_confidence', 5, 2)->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->string('scan_provider')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_attachments');
    }
};

  