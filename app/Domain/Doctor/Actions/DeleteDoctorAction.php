<?php

namespace App\Domain\Doctor\Actions;

use App\Domain\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor;

class DeleteDoctorAction
{
    public function __construct(
        private readonly DoctorRepository $repository
    ) {}

    public function execute(Doctor $doctor): bool
    {
        return $this->repository->delete($doctor);
    }
}