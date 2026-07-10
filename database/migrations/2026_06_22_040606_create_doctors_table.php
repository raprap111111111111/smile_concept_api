<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            
            // ✅ Link to users table (doctor is also a user)
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained()
                  ->onDelete('cascade');
            
            $table->string('license_number')->unique();
            $table->string('specialization')->nullable(); // Orthodontist, Endodontist
            $table->text('bio')->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->string('signature_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};