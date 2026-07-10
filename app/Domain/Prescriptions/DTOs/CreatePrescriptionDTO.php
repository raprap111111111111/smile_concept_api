<?php

namespace App\Domain\Prescriptions\DTOs;

final readonly class CreatePrescriptionDTO
{
    /**
     * @param CreatePrescriptionItemDTO[] $items
     */
    public function __construct(
        public ?int $appointmentId,
        public int $doctorId,
        public int $userId, // Patient ID
        public ?string $notes,
        public array $items
    ) {}
}
