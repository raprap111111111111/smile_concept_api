<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'log_name')) {
                $table->string('log_name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->nullable()->after('log_name');
            }

            if (!Schema::hasColumn('activity_logs', 'event')) {
                $table->string('event')->nullable()->after('description');
            }

            if (!Schema::hasColumn('activity_logs', 'causer_type')) {
                $table->string('causer_type')->nullable()->after('subject_id');
            }

            if (!Schema::hasColumn('activity_logs', 'causer_id')) {
                $table->unsignedBigInteger('causer_id')->nullable()->after('causer_type');
            }

            if (!Schema::hasColumn('activity_logs', 'batch_uuid')) {
                $table->uuid('batch_uuid')->nullable()->after('properties');
            }
        });

        // Make old custom required columns nullable because Spatie will not insert action/user_agent/ip/url by default.
        DB::statement('ALTER TABLE activity_logs MODIFY action VARCHAR(255) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY subject_type VARCHAR(255) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY subject_id BIGINT UNSIGNED NULL');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['causer_type', 'causer_id'], 'activity_logs_causer_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_causer_index');

            $table->dropColumn([
                'log_name',
                'description',
                'event',
                'causer_type',
                'causer_id',
                'batch_uuid',
            ]);
        });
    }
};