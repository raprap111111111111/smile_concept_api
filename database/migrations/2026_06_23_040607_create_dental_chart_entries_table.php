<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dental_chart_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained()->onDelete('cascade');
            $table->string('tooth_number', 5)->index();
            $table->foreignId('tooth_condition_id')->constrained('tooth_conditions')->onDelete('restrict'); // Protect system records
            $table->text('treatment_applied')->nullable();
            $table->timestamps();
            
            $table->unique(['dental_chart_id', 'tooth_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_chart_entries');
    }
};
