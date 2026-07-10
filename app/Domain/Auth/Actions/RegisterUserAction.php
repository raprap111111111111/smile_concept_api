<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Models\User;
use App\Models\PatientProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'phone' => $dto->phone,
            ]);

            PatientProfile::create([
                'user_id' => $user->id,
                'allergies' => null,
                'medical_history' => null,
                'blood_type' => null,
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('patient');
            } else {
                $role = DB::table('roles')->where('name', 'patient')->first();
                if ($role) {
                    DB::table('model_has_roles')->updateOrInsert([
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ]);
                }
            }

            return $user;
        });
    }
}
