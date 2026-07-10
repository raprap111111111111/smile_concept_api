<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tooth_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g., 'root_canal'
            $table->string('label');         // e.g., 'Root Canal Treated'
            $table->string('color_code', 7)->default('#808080'); // hex color for dental chart visualization
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_conditions');
    }
};
