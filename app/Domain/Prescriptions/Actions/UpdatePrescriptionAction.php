<?php

namespace App\Domain\Prescriptions\Actions;

use App\Domain\Prescriptions\DTOs\UpdatePrescriptionDTO;
use App\Domain\Prescriptions\Repositories\PrescriptionRepository;
use App\Domain\Prescriptions\Services\PrescriptionService;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class UpdatePrescriptionAction
{
    public function __construct(
        private readonly PrescriptionRepository $repository,
        private readonly PrescriptionService $service
    ) {}

    public function execute(Prescription $prescription, UpdatePrescriptionDTO $dto)
    {
        if ($dto->items !== null) {
            foreach ($dto->items as $item) {
                $this->service->validateMedicationDuration($item->durationDays);
            }
        }

        return DB::transaction(function () use ($prescription, $dto) {
            // 1. Update Parent fields
            $parentData = array_filter([
                'appointment_id' => $dto->appointmentId,
                'doctor_id' => $dto->doctorId,
                'user_id' => $dto->userId,
                'notes' => $dto->notes,
            ], fn($value) => !is_null($value));

            $this->repository->update($prescription, $parentData);

            // 2. Sync nested line items if provided in request
            if ($dto->items !== null) {
                $prescription->items()->delete(); // Purge old entries

                foreach ($dto->items as $item) {
                    $prescription->items()->create([
                        'medicine_name' => $item->medicineName,
                        'dosage' => $item->dosage,
                        'frequency' => $item->frequency,
                        'duration_days' => $item->durationDays,
                        'instructions' => $item->instructions,
                    ]);
                }
            }

            return $prescription->load(['items', 'patient', 'doctor.user']);
        });
    }
}
