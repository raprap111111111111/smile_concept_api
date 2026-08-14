<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_consents', 'signed_by_staff_id')) {
                $table->foreignId('signed_by_staff_id')
                    ->nullable()
                    ->after('appointment_id')
                    ->constrained('users')
                    ->onDelete('set null')
                    ->comment('Staff member present during in-clinic signing');
            }

            if (! Schema::hasColumn('patient_consents', 'voided_at')) {
                $table->dateTime('voided_at')->nullable()->after('user_agent');
            }

            if (! Schema::hasColumn('patient_consents', 'voided_reason')) {
                $table->string('voided_reason')->nullable()->after('voided_at');
            }

            if (! Schema::hasColumn('patient_consents', 'voided_by')) {
                $table->foreignId('voided_by')
                    ->nullable()
                    ->after('voided_reason')
                    ->constrained('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by_staff_id');
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at', 'voided_reason']);
        });
    }
};
