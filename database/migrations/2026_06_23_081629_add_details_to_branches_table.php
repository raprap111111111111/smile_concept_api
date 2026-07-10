<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Core Identity
            $table->string('branch_code')->unique()->after('name');
            
            // Location
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
            
            // Contact
            $table->string('phone')->nullable()->after('province');
            $table->string('email')->nullable()->after('phone');
            
            // Operational
            $table->boolean('is_active')->default(true)->after('email');
            $table->string('opening_hours')->nullable()->after('is_active');
            
            // Utilities
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'branch_code', 'city', 'province', 'phone', 
                'email', 'is_active', 'opening_hours', 'deleted_at'
            ]);
        });
    }
};