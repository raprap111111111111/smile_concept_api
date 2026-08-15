<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_consents', 'form_data')) {
                $table->json('form_data')
                    ->nullable()
                    ->after('signature_data')
                    ->comment('All checkbox/radio/initial answers captured at sign-time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_consents', function (Blueprint $table) {
            if (Schema::hasColumn('patient_consents', 'form_data')) {
                $table->dropColumn('form_data');
            }
        });
    }
};