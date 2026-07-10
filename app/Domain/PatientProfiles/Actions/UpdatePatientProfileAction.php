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

        $data = array_filter([
            'user_id'                              => $dto->userId,
            'allergies'                            => $dto->allergies,
            'medical_history'                      => $dto->medicalHistory,
            'blood_type'                           => $dto->bloodType,
            'emergency_contact_name'               => $dto->emergencyContactName,
            'emergency_contact_phone'              => $dto->emergencyContactPhone,
            'requires_epinephrine_free_anesthesia' => $dto->requiresEpinephrineFreeAnesthesia,
            'has_cardiac_conditions'               => $dto->hasCardiacConditions,
            'is_pregnant'                          => $dto->isPregnant,
            'has_bleeding_disorders'               => $dto->hasBleedingDisorders,
        ], fn($value) => !is_null($value));
        
        return $this->repository->update($profile, $data);
    }
}
