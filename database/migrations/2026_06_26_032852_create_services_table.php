<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable(); // For cards/previews
            $table->string('icon')->nullable();            // Material icon name e.g. "medical_services"
            $table->string('image')->nullable();           // Image file path
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable(); // For "starts from $X to $Y" range
            $table->integer('duration_minutes')->nullable(); // Estimated treatment time
            $table->string('category')->nullable();         // e.g. "Cosmetic", "Orthodontics"
            $table->boolean('is_featured')->default(false); // Show on homepage prominently
            $table->boolean('is_active')->default(true);    // Visible on landing page
            $table->integer('sort_order')->default(0);      // Display order
            $table->softDeletes();
            $table->timestamps();

            // Indexes for faster queries
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};