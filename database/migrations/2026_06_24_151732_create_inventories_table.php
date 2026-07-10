<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            // Prevent duplicate entries for the same item at the same branch
            $table->unique(['branch_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
