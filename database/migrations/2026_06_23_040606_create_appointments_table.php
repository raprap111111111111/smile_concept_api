<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            $table->text('reason_for_visit')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('status', array_column(AppointmentStatus::cases(), 'value'))
                  ->default(AppointmentStatus::PENDING->value);
            
            $table->boolean('reminder_sent')->default(false);
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['doctor_id', 'start_time']);
            $table->index(['user_id', 'status']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};