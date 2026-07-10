<?php

namespace App\Domain\ClinicalNotes\DTOs;

final readonly class UpdateClinicalNoteDTO
{
    public function __construct(
        public ?int $appointmentId = null,
        public ?int $doctorId = null,
        public ?string $treatmentNotes = null,
        public ?string $postOpInstructions = null,
        public ?bool $isLocked = null
    ) {}
}
