<?php

namespace App\Domain\ClinicalNotes\DTOs;

final readonly class CreateClinicalNoteDTO
{
    public function __construct(
        public int $appointmentId,
        public int $doctorId,
        public string $treatmentNotes,
        public ?string $postOpInstructions = null,
        public bool $isLocked = false
    ) {}
}
