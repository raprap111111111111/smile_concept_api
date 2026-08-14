<?php

namespace App\Events\Settings;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The appointment settings changed. Staff screens showing slot grids or the
 * settings form should refetch — clinic hours, caps or buffers may have moved
 * under them.
 */
class AppointmentSettingsUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<string, mixed> $settings The new flat key => value map.
     */
    public function __construct(
        public array $settings,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('clinic.appointments')];
    }

    public function broadcastAs(): string
    {
        return 'appointment_settings.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['settings' => $this->settings];
    }
}
