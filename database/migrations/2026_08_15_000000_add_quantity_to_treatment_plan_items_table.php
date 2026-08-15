<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The plan builder has always let a clinician set a quantity per step, but the
 * table had nowhere to put it: the create action summed the treatment price
 * once per row, so "3 x fillings" quoted as one filling.
 *
 * `unit_price` freezes the catalog price at quote time — a plan is an offer to
 * the patient, so a later price change in the catalog must not silently move
 * an accepted quote. `estimated_cost` keeps its meaning as the line total
 * (unit_price x quantity), which is what `total_estimated_amount` sums.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('sequence_order');
            $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
        });

        // Existing rows were written one-per-treatment with no multiplication,
        // so their estimated_cost is exactly the unit price.
        DB::table('treatment_plan_items')->update([
            'unit_price' => DB::raw('estimated_cost'),
        ]);
    }

    public function down(): void
    {
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_price']);
        });
    }
};
