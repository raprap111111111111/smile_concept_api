<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What each treatment consumes — the recipe behind automatic stock deduction.
 *
 * `treatments` is a catalog (name, price, duration), which makes it the only
 * stable per-procedure-type thing to hang this off. An extraction always uses
 * roughly the same supplies, so the clinic states that once here instead of
 * re-entering it for every patient.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('treatment_consumables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete, unlike the cascade on treatment_id: deleting a
            // treatment should take its recipe with it, but deleting an item
            // that recipes still reference would silently change what every
            // future procedure deducts.
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            // Integer on purpose. Fractional use ("half a carpule") is modelled
            // by giving the item a finer unit_of_measure — `ml` rather than
            // `bottle` — which matches every other quantity column here and
            // avoids rounding drift through FEFO allocation.
            $table->unsignedInteger('quantity_per_use')->default(1);

            // Skipped by automatic deduction. For supplies used often enough to
            // be worth listing, but not reliably enough to deduct unasked.
            $table->boolean('is_optional')->default(false);

            $table->string('notes')->nullable();
            $table->timestamps();

            // One line per item per treatment — quantity_per_use is the amount.
            $table->unique(['treatment_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_consumables');
    }
};
