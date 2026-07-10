<?php

namespace App\Domain\ClinicalNotes\Actions;

use App\Domain\ClinicalNotes\DTOs\CreateClinicalNoteDTO;
use App\Domain\ClinicalNotes\Repositories\ClinicalNoteRepository;

class CreateClinicalNoteAction
{
    public function __construct(
        private readonly ClinicalNoteRepository $repository
    ) {}

    public function execute(CreateClinicalNoteDTO $dto)
    {
        return $this->repository->create([
            'appointment_id' => $dto->appointmentId,
            'doctor_id' => $dto->doctorId,
            'treatment_notes' => $dto->treatmentNotes,
            'post_op_instructions' => $dto->postOpInstructions,
            'is_locked' => $dto->isLocked,
        ]);
    }
}
