<?php

namespace App\Domain\Doctor\Actions;

use App\Domain\Doctor\DTOs\CreateDoctorDTO;
use App\Domain\Doctor\Repositories\DoctorRepository;

class CreateDoctorAction
{
    public function __construct(
        private readonly DoctorRepository $repository
    ) {}

    public function execute(CreateDoctorDTO $dto)
    {
        return $this->repository->create([
            'user_id' => $dto->userId,
            'specialization' => $dto->specialization,
            'license_number' => $dto->licenseNumber,
        ]);
    }
}