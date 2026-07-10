<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->enum('channel', ['sms', 'email', 'push', 'in_app']);
            $table->enum('status', ['pending', 'sent', 'failed', 'read']);
            $table->dateTime('scheduled_for');
            $table->dateTime('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
    }
};