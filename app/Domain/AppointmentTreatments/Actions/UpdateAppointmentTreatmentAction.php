<?php

namespace App\Domain\AppointmentTreatments\Actions;

use App\Domain\AppointmentTreatments\DTOs\UpdateAppointmentTreatmentDTO;
use App\Domain\AppointmentTreatments\Repositories\AppointmentTreatmentRepository;
use App\Domain\AppointmentTreatments\Services\AppointmentTreatmentService;
use App\Models\AppointmentTreatment;

class UpdateAppointmentTreatmentAction
{
    public function __construct(
        private readonly AppointmentTreatmentRepository $repository,
        private readonly AppointmentTreatmentService    $service,
    ) {}

    public function execute(
        AppointmentTreatment $appointmentTreatment,
        UpdateAppointmentTreatmentDTO $dto
    ): AppointmentTreatment {

        if ($dto->toothNumber !== null) {
            $this->service->validateToothNumber($dto->toothNumber);
        }

        $data = array_filter([
            'appointment_id' => $dto->appointmentId,
            'treatment_id'   => $dto->treatmentId,
            'tooth_number'   => $dto->toothNumber,
            'price_charged'  => $dto->priceCharged,
            'notes'          => $dto->notes,
        ], fn($v) => !is_null($v));

        return $this->repository->update($appointmentTreatment, $data);
    }
}