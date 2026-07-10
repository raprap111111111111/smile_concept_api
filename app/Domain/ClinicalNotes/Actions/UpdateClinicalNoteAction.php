<?php

namespace App\Domain\ClinicalNotes\Actions;

use App\Domain\ClinicalNotes\DTOs\UpdateClinicalNoteDTO;
use App\Domain\ClinicalNotes\Repositories\ClinicalNoteRepository;
use App\Domain\ClinicalNotes\Services\ClinicalNoteService;
use App\Models\ClinicalNote;

class UpdateClinicalNoteAction
{
    public function __construct(
        private readonly ClinicalNoteRepository $repository,
        private readonly ClinicalNoteService $service
    ) {}

    public function execute(ClinicalNote $note, UpdateClinicalNoteDTO $dto)
    {
        $this->service->verifyLockedState($note);

        $data = array_filter([
            'appointment_id' => $dto->appointmentId,
            'doctor_id' => $dto->doctorId,
            'treatment_notes' => $dto->treatmentNotes,
            'post_op_instructions' => $dto->postOpInstructions,
            'is_locked' => $dto->isLocked,
        ], fn($value) => !is_null($value));

        return $this->repository->update($note, $data);
    }
}
