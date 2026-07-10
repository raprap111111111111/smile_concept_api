<?php
// app/Domain/Appointments/DTOs/UpdateAppointmentDTO.php

namespace App\Domain\Appointments\DTOs;

use App\Enums\AppointmentStatus;

final readonly class UpdateAppointmentDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?int $doctorId = null,
        public ?int $branchId = null,
        public ?string $startTime = null,
        public ?string $endTime = null,
        public ?AppointmentStatus $status = null,
        public ?string $reasonForVisit = null,      
        public ?string $cancellationReason = null, 
        public ?bool $reminderSent = null,
    ) {}
}