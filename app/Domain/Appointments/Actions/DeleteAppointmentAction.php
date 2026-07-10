<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Models\Appointment;

class DeleteAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $repository
    ) {}

    public function execute(Appointment $appointment): bool
    {
        return $this->repository->delete($appointment);
    }
}
