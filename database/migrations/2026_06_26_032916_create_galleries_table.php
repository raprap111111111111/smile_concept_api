<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image');                       // Required - main image file
            $table->string('thumbnail')->nullable();       // Optional smaller version
            $table->string('category')->default('clinic'); // clinic, equipment, before-after, team
            $table->string('alt_text')->nullable();        // For SEO/accessibility
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
            $table->index('category');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};