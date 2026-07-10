<?php

namespace App\Domain\Prescriptions\DTOs;

final readonly class UpdatePrescriptionDTO
{
    /**
     * @param CreatePrescriptionItemDTO[]|null $items
     */
    public function __construct(
        public ?int $appointmentId = null,
        public ?int $doctorId = null,
        public ?int $userId = null,
        public ?string $notes = null,
        public ?array $items = null
    ) {}
}
