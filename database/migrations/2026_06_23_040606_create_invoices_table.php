<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained();
            $table->string('invoice_number')->unique()->nullable(); // ✅ INV-2025-0001
            $table->decimal('total_amount', 10, 2);
            $table->decimal('balance_due', 10, 2);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled'])
                ->default('unpaid')
                ->index();
        
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable(); // ✅ For overdue tracking
            $table->softDeletes();
            $table->timestamp('last_overdue_notification_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
