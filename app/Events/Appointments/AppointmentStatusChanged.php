<?php

namespace App\Events\Appointments;

use App\Http\Resources\v1\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * An appointment moved between statuses (pending → confirmed / cancelled /
 * completed).
 *
 * This is the cross-user case the whole feature exists for: a patient books and
 * waits while an admin on another device approves, and until now had no way to
 * find out short of navigating away and back.
 */
class AppointmentStatusChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array<string, mixed> */
    public array $payload;

    public ?int $patientUserId;

    public function __construct(
        Appointment $appointment,
        public string $previousStatus,
    ) {
        $this->payload = (new AppointmentResource($appointment))->resolve();
        $this->patientUserId = $appointment->user_id !== null
            ? (int) $appointment->user_id
            : null;
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('clinic.appointments')];

        if ($this->patientUserId !== null) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->patientUserId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'appointment.status_changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'appointment'     => $this->payload,
            'previous_status' => $this->previousStatus,
        ];
    }
}
