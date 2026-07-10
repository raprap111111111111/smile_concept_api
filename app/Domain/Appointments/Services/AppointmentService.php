<?php

namespace App\Domain\Appointments\Services;

use App\Domain\Appointments\DTOs\CreateAppointmentDTO;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Validate appointment time
     */
    public function validateAppointmentTime(CreateAppointmentDTO $dto): void
    {
        $startTime = Carbon::parse($dto->startTime);
        $endTime = Carbon::parse($dto->endTime);

        if ($endTime->lte($startTime)) {
            throw new \Exception('End time must be after start time');
        }

        if ($startTime->isPast()) {
            throw new \Exception('Cannot book appointments in the past');
        }

        // Check minimum duration (30 minutes)
        $minDuration = $startTime->copy()->addMinutes(30);
        if ($endTime->lt($minDuration)) {
            throw new \Exception('Appointment must be at least 30 minutes');
        }
    }

    /**
     * Validate doctor availability at branch
     */
    public function validateDoctorAvailability(CreateAppointmentDTO $dto): void
    {
        // Add business logic for doctor availability at specific branch
        // e.g., check if doctor works at that branch on that day
        // This can be extended based on your requirements
    }

    /**
     * Send confirmation email
     */
    public function sendConfirmationEmail(int $appointmentId): void
    {
        // Implement email sending logic
        // Mail::to($appointment->user->email)->send(new AppointmentConfirmation($appointment));
    }
}
