<?php
// app/Domain/Appointments/DTOs/CreateAppointmentDTO.php

namespace App\Domain\Appointments\DTOs;

use App\Enums\AppointmentStatus;

final readonly class CreateAppointmentDTO
{
    public function __construct(
        public int $doctorId,
        public int $branchId,
        public string $startTime,
        public string $endTime,
        public AppointmentStatus $status,
        public ?int $userId = null,
        public ?int $createdBy = null,
        public ?string $reasonForVisit = null,
        public ?bool $reminderSent = null,
        public ?string $patientName = null,
        public ?string $patientPhone = null,
        public ?string $patientEmail = null,
        public ?string $additionalNotes = null,
    ) {}
}