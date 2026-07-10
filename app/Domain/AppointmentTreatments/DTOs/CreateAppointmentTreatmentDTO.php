<?php

namespace App\Domain\AppointmentTreatments\DTOs;

final readonly class CreateAppointmentTreatmentDTO
{
    public function __construct(
        public int     $appointmentId,
        public int     $treatmentId,
        public ?string $toothNumber = null,
        public ?float  $priceCharged = null, // Auto-filled from treatment if null
        public ?string $notes = null,
    ) {}
}