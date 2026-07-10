<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recall_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g., 'cleaning', 'ortho_check'
            $table->string('label');         // e.g., 'Standard Prophylaxis'
            $table->unsignedInteger('frequency_months')->default(6); // Standard recall interval helper
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recall_types');
    }
};
