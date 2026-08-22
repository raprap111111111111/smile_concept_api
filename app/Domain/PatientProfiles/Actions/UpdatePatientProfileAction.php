<?php

namespace App\Domain\PatientProfiles\Actions;

use App\Domain\PatientProfiles\DTOs\UpdatePatientProfileDTO;
use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Domain\PatientProfiles\Services\PatientProfileService;
use App\Models\PatientProfile;
use Illuminate\Support\Facades\DB;

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

        if ($dto->userId !== null && $dto->userId !== $profile->user_id) {
            if ($this->repository->hasExistingProfile($dto->userId, $profile->id)) {
                throw new \DomainException("A medical profile is already assigned to this patient.");
            }
        }

        $data = array_filter([
            // Demographics & Address
            'date_of_birth'                        => $dto->dateOfBirth,
            'gender'                               => $dto->gender,
            'civil_status'                         => $dto->civilStatus,
            'nationality'                          => $dto->nationality,
            'occupation'                           => $dto->occupation,
            'address'                              => $dto->address,
            'city'                                 => $dto->city,
            'province'                             => $dto->province,
            'postal_code'                          => $dto->postalCode,

            // Insurance & Referral
            'insurance_provider'                  => $dto->insuranceProvider,
            'insurance_number'                    => $dto->insuranceNumber,
            'referred_by'                          => $dto->referredBy,

            // Medical fields
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

        if ($dto->userId !== null) {
            $data['user_id'] = $dto->userId;
        }

        $userData = array_filter([
            'name'  => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
        ], fn($key) => in_array($key, $dto->providedKeys, true), ARRAY_FILTER_USE_KEY);

        if ($userData === []) {
            return $this->repository->update($profile, $data);
        }

        return DB::transaction(function () use ($profile, $data, $userData) {
            $profile->user?->update($userData);

            return $this->repository->update($profile, $data);
        });
    }
}