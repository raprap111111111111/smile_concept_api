<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in order
        $this->call([
            PermissionSeeder::class,    
            RoleSeeder::class,          
            RolePermissionSeeder::class, 
            SuperAdminSeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
            RecallTypeSeeder::class,
            DoctorSeeder::class,
            ToothConditionSeeder::class,
            TreatmentSeeder::class,  
            ConsentTemplateSeeder::class, 
            SettingSeeder::class,
            NotificationTemplateSeeder::class,



 
        ]);
    }
}