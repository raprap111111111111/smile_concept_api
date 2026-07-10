<?php

namespace App\Domain\AppointmentTreatments\Actions;

use App\Domain\AppointmentTreatments\Repositories\AppointmentTreatmentRepository;
use App\Models\AppointmentTreatment;

class DeleteAppointmentTreatmentAction
{
    public function __construct(
        private readonly AppointmentTreatmentRepository $repository,
    ) {}

    public function execute(AppointmentTreatment $appointmentTreatment): bool
    {
        return (bool) $this->repository->delete($appointmentTreatment);
    }
}