<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\Appointments\DTOs\CalendarCountsAppointmentDTO;
use App\Domain\Appointments\Repositories\AppointmentRepository;

class GetAppointmentCalendarCountsAction
{
    public function __construct(
        private readonly AppointmentRepository $repository,
    ) {}

    public function execute(
        CalendarCountsAppointmentDTO $dto,
        bool $canViewAny = false,      // ✅ NEW
        ?int $authUserId = null         // ✅ NEW
    ): array {
        return $this->repository->getCalendarCounts(
            $dto,
            $canViewAny,
            $authUserId,
        );
    }
}