<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consent_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->longText('body');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('patient_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consent_template_id')
                ->constrained('consent_templates')
                ->onDelete('restrict');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // patient
            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained('appointments')
                ->onDelete('set null');
            $table->foreignId('signed_by_staff_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Staff member present during in-clinic signing');
            $table->dateTime('signed_at');
            $table->longText('signature_data')
                ->comment('Base64 image data URI from signature pad');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Voiding fields
            $table->dateTime('voided_at')->nullable();
            $table->string('voided_reason')->nullable();
            $table->foreignId('voided_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            $table->index(['user_id', 'signed_at']);
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_consents');
        Schema::dropIfExists('consent_templates');
    }
};