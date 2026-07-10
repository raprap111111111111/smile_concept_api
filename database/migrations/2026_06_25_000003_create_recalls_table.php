<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recalls', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Patient
            $table->foreignId('recall_type_id')->constrained('recall_types')->onDelete('restrict');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            
            $table->date('due_date')->index();
            $table->string('status')->default('pending'); // pending, notified, scheduled, overdue
            $table->dateTime('last_notified_at')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['due_date', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recalls');
    }
};