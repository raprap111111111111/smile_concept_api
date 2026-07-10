<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->string('medicine_name');
            $table->string('dosage')->comment('e.g., 500mg, 1 tablet');
            $table->string('frequency')->comment('e.g., 3 times daily, every 8 hours');
            $table->unsignedInteger('duration_days')->comment('e.g., 7');
            $table->text('instructions')->nullable()->comment('e.g., take after meal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
