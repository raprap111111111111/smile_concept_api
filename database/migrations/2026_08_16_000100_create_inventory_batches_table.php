<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A batch is one delivery of one item into one branch: a lot number, an expiry
 * date, and what is left of it.
 *
 * `inventories` cannot express this — it carries unique(branch_id, item_id) and
 * a single expiry_date, so two lots of the same anesthetic with different expiry
 * dates have nowhere to live. That table stays as the per-branch running total;
 * this is the layer underneath it that consumption actually draws from.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            // Supplier lot/batch code as printed on the box. Free text: it is a
            // reference for humans reconciling against a physical shelf, not a key.
            $table->string('lot_number')->nullable();

            // Null means non-perishable. FEFO consumes those last.
            $table->date('expiry_date')->nullable();

            $table->unsignedInteger('quantity_received');

            // Never negative. A consumption that outruns stock records the
            // unmet remainder as a batch-less movement instead, so this column
            // always reflects something physically on a shelf.
            $table->unsignedInteger('quantity_remaining');

            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            // The FEFO lookup: narrow to a branch and item, order by expiry.
            $table->index(['branch_id', 'item_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
