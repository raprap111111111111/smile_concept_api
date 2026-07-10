<?php

namespace App\Domain\Doctor\Actions;

use App\Domain\Doctor\DTOs\UpdateDoctorDTO;
use App\Domain\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor;

class UpdateDoctorAction
{
    public function __construct(
        private readonly DoctorRepository $repository
    ) {}

    public function execute(Doctor $doctor, UpdateDoctorDTO $dto)
    {
        $data = [
            'specialization' => $dto->specialization,
            'license_number' => $dto->licenseNumber,
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        return $this->repository->update($doctor, $data);
    }
}