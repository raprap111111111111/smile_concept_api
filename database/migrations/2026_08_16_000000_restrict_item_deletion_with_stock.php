<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * inventories.item_id was ON DELETE CASCADE, so removing one catalog row
 * silently wiped that item's stock at every branch — no warning, no audit
 * trail, no way back. Stock is the record of something physically present in a
 * cabinet; it must outlive an edit to the catalog.
 *
 * DeleteItemAction now refuses up front with a readable message. This
 * constraint is the backstop for anything that bypasses it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
        });
    }
};
