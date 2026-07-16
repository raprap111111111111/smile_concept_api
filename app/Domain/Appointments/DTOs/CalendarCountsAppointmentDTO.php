<?php

namespace App\Domain\Appointments\DTOs;

final readonly class CalendarCountsAppointmentDTO
{
    public const SCOPE_OWN = 'own';

    /**
     * Clinic-wide day load. For a caller without appointment.viewAny this
     * returns a per-day total only, so patients can see how busy a day is
     * while booking without reading anyone else's appointment detail.
     */
    public const SCOPE_CLINIC = 'clinic';

    public function __construct(
        public string $month,
        public ?string $status = null,
        public ?int $doctorId = null,
        public ?int $branchId = null,
        public ?int $userId = null,
        public string $scope = self::SCOPE_OWN,
    ) {}
}