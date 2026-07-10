<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Patient
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('restrict');
            $table->string('name'); // e.g., "Full Upper Arch Restorative Plan"
            $table->string('status')->default('proposed'); // draft, proposed, accepted, completed, rejected
            $table->decimal('total_estimated_amount', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('treatment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->onDelete('cascade');
            $table->foreignId('treatment_id')->constrained('treatments')->onDelete('restrict');
            $table->integer('sequence_order')->default(1)->comment('Determines the step sequence (e.g., Step 1, Step 2)');
            $table->decimal('estimated_cost', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_items');
        Schema::dropIfExists('treatment_plans');
    }
};
