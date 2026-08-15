<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only ledger. Every change to stock lands here, and
 * `inventories.quantity` is derived from it — the sum of quantity_delta for a
 * branch and item IS the quantity on hand.
 *
 * Before this table, quantity was overwritten with an absolute value and the
 * only trace was a generic activity-log row, which cannot be aggregated into a
 * balance or answered "where did the anesthetic go".
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            // Null only for a shortfall — the part of a consumption that no
            // batch could satisfy. Keeps quantity_remaining unsigned and honest
            // while still letting the branch total go negative.
            $table->foreignId('inventory_batch_id')->nullable()
                ->constrained('inventory_batches')->nullOnDelete();

            $table->enum('type', [
                'opening_balance',
                'stock_in',
                'consumption',
                'manual_usage',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'expired_writeoff',
            ]);

            // Signed: positive adds, negative removes. Direction lives here
            // rather than being inferred from `type`, so a stock count that
            // corrects downward is the same shape as one that corrects upward.
            $table->integer('quantity_delta');

            // Branch+item running total immediately after this row. Denormalised
            // so the ledger reads as a statement without re-summing every time.
            $table->integer('balance_after');

            $table->string('reason')->nullable();

            // What caused this — an Appointment for automatic consumption.
            // Nullable morph: a manual stock-in has no clinical cause.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->foreignId('performed_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'item_id', 'created_at']);

            // Drives the idempotency check that stops a repeated
            // "mark completed" from deducting the same appointment twice.
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
