<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consent_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique(); // e.g., "Informed Consent for Surgical Extraction"
            $table->longText('body'); // The legally binding clinical policy body text
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('patient_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consent_template_id')->constrained('consent_templates')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Patient
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->dateTime('signed_at');
            $table->longText('signature_data')->comment('Stores Base64, raw SVG coordinates, or biometric signature vector values');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_consents');
        Schema::dropIfExists('consent_templates');
    }
};
