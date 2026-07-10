<?php

namespace App\Domain\Prescriptions\Actions;

use App\Domain\Prescriptions\DTOs\CreatePrescriptionDTO;
use App\Domain\Prescriptions\Repositories\PrescriptionRepository;
use App\Domain\Prescriptions\Services\PrescriptionService;
use Illuminate\Support\Facades\DB;

class CreatePrescriptionAction
{
    public function __construct(
        private readonly PrescriptionRepository $repository,
        private readonly PrescriptionService $service
    ) {}

    public function execute(CreatePrescriptionDTO $dto)
    {
        foreach ($dto->items as $item) {
            $this->service->validateMedicationDuration($item->durationDays);
        }

        return DB::transaction(function () use ($dto) {
            // 1. Log Parent Prescription Header
            $prescription = $this->repository->create([
                'appointment_id' => $dto->appointmentId,
                'doctor_id' => $dto->doctorId,
                'user_id' => $dto->userId,
                'notes' => $dto->notes,
            ]);

            // 2. Insert Nested Medications
            foreach ($dto->items as $item) {
                $prescription->items()->create([
                    'medicine_name' => $item->medicineName,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration_days' => $item->durationDays,
                    'instructions' => $item->instructions,
                ]);
            }

            return $prescription->load(['items', 'patient', 'doctor.user']);
        });
    }
}
