<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('restrict');
            $table->longText('treatment_notes');
            $table->longText('post_op_instructions')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
