<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->unique()
                  ->constrained()
                  ->onDelete('cascade');

            // ─── Demographics ────────────────────────────
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('civil_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();

            // ─── Address ─────────────────────────────────
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();

            // ─── Medical Info ────────────────────────────
            $table->string('blood_type', 5)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('dental_history')->nullable();

            // ─── Medical Alert Flags ✅ ADDED ────────────
            $table->boolean('requires_epinephrine_free_anesthesia')->default(false);
            $table->boolean('has_cardiac_conditions')->default(false);
            $table->boolean('is_pregnant')->default(false);
            $table->boolean('has_bleeding_disorders')->default(false);

            // ─── Emergency Contact ───────────────────────
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // ─── Insurance ───────────────────────────────
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_number')->nullable();

            // ─── Referral ────────────────────────────────
            $table->string('referred_by')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};