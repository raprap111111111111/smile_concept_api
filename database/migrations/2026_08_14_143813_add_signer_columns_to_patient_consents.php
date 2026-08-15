<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {

            // Guardian who signed on behalf of minor/dependent patient
            if (! Schema::hasColumn('patient_consents', 'signed_by_guardian_id')) {
                $table->foreignId('signed_by_guardian_id')
                    ->nullable()
                    ->after('signed_by_staff_id')
                    ->constrained('users')
                    ->onDelete('set null')
                    ->comment('Guardian who signed for a minor/dependent patient');
            }

            // Track who signed: 'self' | 'guardian' | 'staff'
            if (! Schema::hasColumn('patient_consents', 'signer_relationship')) {
                $table->string('signer_relationship', 20)
                    ->nullable()
                    ->after('signed_by_guardian_id')
                    ->comment('self | guardian | staff');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {
            if (Schema::hasColumn('patient_consents', 'signer_relationship')) {
                $table->dropColumn('signer_relationship');
            }

            if (Schema::hasColumn('patient_consents', 'signed_by_guardian_id')) {
                $table->dropConstrainedForeignId('signed_by_guardian_id');
            }
        });
    }
};