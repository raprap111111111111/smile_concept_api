<?php

namespace App\Domain\PatientProfiles\Actions;

use App\Domain\PatientProfiles\DTOs\CreatePatientProfileDTO;
use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Domain\PatientProfiles\Services\PatientProfileService;
use App\Models\PatientProfile;
use App\Models\User;
use App\Notifications\NewPatientRegisteredNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CreatePatientProfileAction
{
    public function __construct(
        private readonly PatientProfileRepository $repository,
        private readonly PatientProfileService    $service,
    ) {}

    public function execute(CreatePatientProfileDTO $dto): PatientProfile
    {
        // ─── 1. Validate emergency phone ────────────────────
        if ($dto->emergencyContactPhone !== null) {
            $this->service->validateContactPhone($dto->emergencyContactPhone);
        }

        // ─── 2. Create user + profile in transaction ────────
        $profile = DB::transaction(function () use ($dto) {

            // Create User account
            $user = User::create([
                'name'     => $dto->name,
                'email'    => $dto->email,
                'phone'    => $dto->phone,
                'password' => Hash::make($dto->password ?? Str::random(12)),
            ]);

            // Assign patient role (Spatie)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('patient');
            }

            // Create medical profile
            return $this->repository->create([
                'user_id'                              => $user->id,
                'allergies'                            => $dto->allergies,
                'medical_history'                      => $dto->medicalHistory,
                'blood_type'                           => $dto->bloodType,
                'emergency_contact_name'               => $dto->emergencyContactName,
                'emergency_contact_phone'              => $dto->emergencyContactPhone,
                'requires_epinephrine_free_anesthesia' => $dto->requiresEpinephrineFreeAnesthesia,
                'has_cardiac_conditions'               => $dto->hasCardiacConditions,
                'is_pregnant'                          => $dto->isPregnant,
                'has_bleeding_disorders'               => $dto->hasBleedingDisorders,
            ]);
        });

        // ─── 3. Load patient relation for notification ──────
        $profile->load('user');

        // ─── 4. Notify admins (OUTSIDE transaction) ─────────
        $this->notifyAdmins($profile->user);

        return $profile;
    }

    /**
     * Notify all admin users about the new patient.
     */
    private function notifyAdmins(User $patient): void
    {
        $admins = $this->getAdmins();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send(
            $admins,
            new NewPatientRegisteredNotification($patient)
        );
    }

    /**
     * Get all users with the 'admin' role.
     *
     * @return Collection<int, User>
     */
    private function getAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->get();
    }
}