<?php

namespace App\Domain\PatientProfiles\Actions;

use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Models\PatientProfile;
use Illuminate\Support\Facades\DB;

class DeletePatientProfileAction
{
    public function __construct(
        private readonly PatientProfileRepository $repository
    ) {}

    public function execute(PatientProfile $patientProfile): bool
    {
        return DB::transaction(function () use ($patientProfile) {
            $user = $patientProfile->user;

            // Delete the medical profile via repository
            $this->repository->delete($patientProfile);

            // Then delete the associated user account
            if ($user) {
                $user->delete();
            }

            return true;
        });
    }
}