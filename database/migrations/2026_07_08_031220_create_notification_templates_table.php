<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();

                $table->string('key')->unique();
                $table->string('name');
                $table->string('subject')->nullable();
                $table->longText('body');

                $table->json('channels')->nullable(); // ["mail", "database"]
                $table->json('variables')->nullable(); // ["patient_name", "appointment_date"]

                $table->string('trigger_event')->nullable();
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->index('key');
                $table->index('trigger_event');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};