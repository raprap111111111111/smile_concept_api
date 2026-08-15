<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many times a procedure was performed at one appointment.
 *
 * Mirrors the `quantity` added to treatment_plan_items. Until now three
 * fillings meant three rows, which works for billing but leaves stock
 * deduction guessing.
 *
 * Stock math only: `price_charged` keeps its existing meaning as the amount
 * charged for the row, and no billing code reads this column. Changing that
 * would silently re-price historical appointments.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_treatments', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('tooth_number');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_treatments', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
