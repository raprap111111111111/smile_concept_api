<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure required branches (1 and 2) exist
        $branchIds = [1, 2];
        foreach ($branchIds as $id) {
            Branch::firstOrCreate(
                ['id' => $id],
                [
                    'name' => "Smile Concept Branch " . $id,
                    'branch_code' => "SC-BR" . $id,
                    'address' => "Default Branch Address " . $id,
                    'is_active' => true,
                ]
            );
        }

        // 2. Create the Doctor User
        $user = User::updateOrCreate(
            ['email' => 'juvileannmansader@gmail.com'],
            [
                'name' => 'DR. JUVILE ANN LEGISLADOR MANSADER',
                'password' => Hash::make('Password'),
                'phone' => '0994 366 5968',
            ]
        );

        // 3. Attach Branches
        if (method_exists($user, 'branches')) {
            $user->branches()->sync($branchIds);
        }

        // 4. Safely assign the "dentist" role
        // Since User model defaults to 'api', we only pass the role name.
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('dentist');
        }

        // 5. Create the Receptionist / front desk user
        $receptionist = User::updateOrCreate(
            ['email' => 'receptionist@smileconcept.com'],
            [
                'name'     => 'Front Desk Receptionist',
                'password' => Hash::make('Password'),
                'phone'    => '0900 000 0000',
            ]
        );

        $receptionist->branches()->sync($branchIds);
        $receptionist->assignRole('receptionist');
    }
}