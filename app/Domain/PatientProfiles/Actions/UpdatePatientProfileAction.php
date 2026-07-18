<?php

namespace App\Domain\PatientProfiles\Actions;

use App\Domain\PatientProfiles\DTOs\UpdatePatientProfileDTO;
use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Domain\PatientProfiles\Services\PatientProfileService;
use App\Models\PatientProfile;

class UpdatePatientProfileAction
{
    public function __construct(
        private readonly PatientProfileRepository $repository,
        private readonly PatientProfileService    $service,
    ) {}

    public function execute(PatientProfile $profile, UpdatePatientProfileDTO $dto): PatientProfile
    {
        if ($dto->emergencyContactPhone !== null) {
            $this->service->validateContactPhone($dto->emergencyContactPhone);
        }

        // ✅ Only check duplicates if user_id is actually being changed
        if ($dto->userId !== null && $dto->userId !== $profile->user_id) {
            if ($this->repository->hasExistingProfile($dto->userId, $profile->id)) {
                throw new \DomainException("A medical profile is already assigned to this patient.");
            }
        }

        // Keyed off what the client actually sent, not off null: a nullable
        // field such as the emergency contact has to be clearable, and
        // filtering nulls away would make that impossible.
        $data = array_filter([
            'allergies'                            => $dto->allergies,
            'medical_history'                      => $dto->medicalHistory,
            'blood_type'                           => $dto->bloodType,
            'emergency_contact_name'               => $dto->emergencyContactName,
            'emergency_contact_phone'              => $dto->emergencyContactPhone,
            'requires_epinephrine_free_anesthesia' => $dto->requiresEpinephrineFreeAnesthesia,
            'has_cardiac_conditions'               => $dto->hasCardiacConditions,
            'is_pregnant'                          => $dto->isPregnant,
            'has_bleeding_disorders'               => $dto->hasBleedingDisorders,
        ], fn($key) => in_array($key, $dto->providedKeys, true), ARRAY_FILTER_USE_KEY);

        // Reassigning the owner is not something a client clears, so it keeps
        // the null guard.
        if ($dto->userId !== null) {
            $data['user_id'] = $dto->userId;
        }


        return $this->repository->update($profile, $data);
    }
}
