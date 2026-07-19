<?php

namespace App\Domain\AppointmentReminders\Repositories;

use App\Models\AppointmentReminder;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class AppointmentReminderRepository extends BaseRepository
{
    protected string $model = AppointmentReminder::class;

    protected array $with = ['appointment'];

    protected array $searchable = [
        'error_message',
    ];

    protected array $filterable = [
        'appointment_id',
        'channel',
        'status',
    ];

    protected array $sortable = [
        'id',
        'scheduled_for',
        'sent_at',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'scheduled_for';
    protected string $defaultOrderDirection = 'asc';

    /**
     * Cancel (delete) all pending reminders for an appointment.
     * Used when an appointment is cancelled or rescheduled.
     *
     * @return int Number of reminders removed
     */
    public function cancelPendingForAppointment(int $appointmentId): int
    {
        return AppointmentReminder::query()
            ->where('appointment_id', $appointmentId)
            ->where('status', 'pending')
            ->delete();
    }

    /**
     * Get all reminders due for dispatch right now.
     */
    public function getDueReminders(int $limit = 100): Collection
    {
        return AppointmentReminder::query()
            ->with('appointment.user')
            ->where('status', 'pending')
            ->where('scheduled_for', '<=', now())
            ->limit($limit)
            ->get();
    }
}