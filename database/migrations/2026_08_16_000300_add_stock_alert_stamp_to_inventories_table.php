<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cooldown stamp for the low-stock digest.
 *
 * The alert command stamps this *before* notifying, following
 * SendAppointmentFollowUpsCommand: notifications are queued, so a crash between
 * the send and the save would re-alert on the next run.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->timestamp('last_low_stock_alert_at')->nullable()->after('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('last_low_stock_alert_at');
        });
    }
};
