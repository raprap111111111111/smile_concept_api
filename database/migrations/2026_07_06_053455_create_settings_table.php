<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('group')->default('general')->index();
            $table->enum('type', [
                'string',
                'integer',
                'float',
                'boolean',
                'json',
                'date'
            ])->default('string');
            $table->string('label')->nullable();          // ✅ "Tax Rate (%)"
            $table->text('description')->nullable();      // ✅ tooltip
            $table->boolean('is_public')->default(false); // ✅ can frontend fetch it?
            $table->boolean('is_editable')->default(true); // ✅ lock system settings
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
