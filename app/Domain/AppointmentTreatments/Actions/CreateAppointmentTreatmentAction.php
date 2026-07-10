<?php

namespace App\Domain\AppointmentTreatments\Actions;

use App\Domain\AppointmentTreatments\DTOs\CreateAppointmentTreatmentDTO;
use App\Domain\AppointmentTreatments\Repositories\AppointmentTreatmentRepository;
use App\Domain\AppointmentTreatments\Services\AppointmentTreatmentService;
use App\Models\AppointmentTreatment;
use Illuminate\Support\Facades\DB;

class CreateAppointmentTreatmentAction
{
    public function __construct(
        private readonly AppointmentTreatmentRepository $repository,
        private readonly AppointmentTreatmentService    $service,
    ) {}

    public function execute(CreateAppointmentTreatmentDTO $dto): AppointmentTreatment
    {
        $this->service->validateToothNumber($dto->toothNumber);

        $priceCharged = $this->service->resolvePrice($dto->treatmentId, $dto->priceCharged);

        return DB::transaction(function () use ($dto, $priceCharged) {
            return $this->repository->create([
                'appointment_id' => $dto->appointmentId,
                'treatment_id'   => $dto->treatmentId,
                'tooth_number'   => $dto->toothNumber,
                'price_charged'  => $priceCharged,
                'notes'          => $dto->notes,
            ]);
        });
    }
}