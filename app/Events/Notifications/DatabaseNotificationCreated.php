<?php

namespace App\Events\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A database notification row was just created for a user.
 *
 * Dispatched by App\Listeners\BroadcastDatabaseNotification, which hooks
 * Laravel's own NotificationSent — so every current and future notification
 * class is covered without touching app/Notifications/.
 */
class DatabaseNotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int  $userId  Recipient. Also the channel scope.
     * @param  array<string, mixed>  $notification  NotificationResource shape.
     * @param  int  $unreadCount  Authoritative badge value after this insert.
     */
    public function __construct(
        public int $userId,
        public array $notification,
        public int $unreadCount,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * The unread count is sent alongside the row so clients can *set* the badge
     * rather than increment it. Incrementing drifts whenever a client misses an
     * event (Reverb keeps no history) or the user has several tabs open.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notification'  => $this->notification,
            'unread_count'  => $this->unreadCount,
        ];
    }
}
