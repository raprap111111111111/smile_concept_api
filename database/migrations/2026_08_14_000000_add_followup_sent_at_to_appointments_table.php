<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Idempotency guard for the aftercare follow-up email: the
            // send-followups command only picks rows where this is NULL.
            $table->dateTime('followup_sent_at')->nullable()->after('reminder_sent');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('followup_sent_at');
        });
    }
};
