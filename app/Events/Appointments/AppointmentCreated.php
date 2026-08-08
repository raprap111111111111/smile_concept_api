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
 * A new appointment was booked.
 *
 * Goes to the shared staff feed so a receptionist sees walk-in and self-service
 * bookings appear, and to the patient's own channel so their list stays current.
 */
class AppointmentCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array<string, mixed> */
    public array $payload;

    public ?int $patientUserId;

    public function __construct(Appointment $appointment)
    {
        // Serialised eagerly rather than holding the model: the payload must be
        // byte-identical to the HTTP representation, and building it here means
        // the queue never has to reload relations that were already loaded.
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

        // Guest bookings have no user_id — patient_name/phone/email carry the
        // details instead — so there is no per-user channel to target.
        if ($this->patientUserId !== null) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->patientUserId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'appointment.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['appointment' => $this->payload];
    }
}
