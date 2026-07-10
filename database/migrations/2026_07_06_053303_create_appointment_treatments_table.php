<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('treatment_id')->constrained()->onDelete('restrict');
            $table->string('tooth_number', 5)->nullable();
            $table->decimal('price_charged', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_treatments');
    }
};