<?php

namespace App\Http\Controllers\v1;

use App\Domain\AppointmentReminders\Repositories\AppointmentReminderRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AppointmentReminder\GetAllAppointmentRemindersRequest;
use App\Http\Requests\v1\AppointmentReminder\GetAppointmentReminderRequest;
use App\Http\Resources\v1\AppointmentReminderResource;
use App\Models\AppointmentReminder;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AppointmentReminderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AppointmentReminderRepository $repository,
    ) {}

    public function index(GetAllAppointmentRemindersRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            AppointmentReminderResource::class
        );

        return $this->successResponse($result, 'Appointment reminders retrieved.');
    }

    public function show(GetAppointmentReminderRequest $request, AppointmentReminder $appointmentReminder): JsonResponse
    {
        return $this->successResponse(
            new AppointmentReminderResource($appointmentReminder->load('appointment')),
            'Appointment reminder details retrieved.'
        );
    }
}