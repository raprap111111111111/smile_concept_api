<?php

namespace App\Domain\AppointmentTreatments\DTOs;

final readonly class UpdateAppointmentTreatmentDTO
{
    public function __construct(
        public ?int    $appointmentId = null,
        public ?int    $treatmentId = null,
        public ?string $toothNumber = null,
        public ?float  $priceCharged = null,
        public ?string $notes = null,
    ) {}
}