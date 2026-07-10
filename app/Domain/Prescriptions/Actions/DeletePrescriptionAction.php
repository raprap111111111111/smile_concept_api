<?php

namespace App\Domain\Prescriptions\Actions;

use App\Domain\Prescriptions\Repositories\PrescriptionRepository;
use App\Models\Prescription;

class DeletePrescriptionAction
{
    public function __construct(
        private readonly PrescriptionRepository $repository
    ) {}

    public function execute(Prescription $prescription): bool
    {
        return $this->repository->delete($prescription);
    }
}
