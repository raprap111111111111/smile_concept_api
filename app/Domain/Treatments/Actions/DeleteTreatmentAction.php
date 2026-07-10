<?php

namespace App\Domain\Treatments\Actions;

use App\Domain\Treatments\Repositories\TreatmentRepository;
use App\Models\Treatment;

class DeleteTreatmentAction
{
    public function __construct(
        private readonly TreatmentRepository $repository
    ) {}

    public function execute(Treatment $treatment): bool
    {
        // Safe delete: Will throw DB restriction if treatment has dependent billing records
        return $this->repository->delete($treatment);
    }
}
