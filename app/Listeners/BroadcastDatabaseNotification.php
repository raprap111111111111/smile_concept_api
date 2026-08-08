<?php

namespace App\Listeners;

use App\Events\Notifications\DatabaseNotificationCreated;
use App\Http\Resources\v1\NotificationResource;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * Rebroadcasts every database notification over the recipient's private channel.
 *
 * Every class in app/Notifications/ returns ['database'] from via(), and Laravel
 * fires NotificationSent once per channel delivery. Hooking that one event covers
 * all of them — and any added later — so neither the notification classes nor the
 * actions that dispatch them need to know broadcasting exists.
 *
 * Auto-discovered: Application::configure() calls withEvents(), and
 * EventServiceProvider discovers listeners in app/Listeners by default. No
 * registration needed.
 */
class BroadcastDatabaseNotification
{
    public function handle(NotificationSent $event): void
    {
        // The same notification can go out over several channels; only the
        // database one has a row worth pushing to the bell.
        if ($event->channel !== 'database') {
            return;
        }

        // notifiable is a morph, so it is not guaranteed to be a User.
        if (! $event->notifiable instanceof User) {
            return;
        }

        // DatabaseChannel::send() returns the row it just created, so there is
        // nothing to re-query here.
        $record = $event->response;

        if (! $record instanceof DatabaseNotification) {
            return;
        }

        DatabaseNotificationCreated::dispatch(
            (int) $event->notifiable->getKey(),
            // Same resource the HTTP endpoints use, so a socket-delivered row and
            // a fetched one are byte-identical. Notably type is class_basename()
            // and the timestamps are Y-m-d H:i:s, neither of which is obvious.
            (new NotificationResource($record))->resolve(),
            $event->notifiable->unreadNotifications()->count(),
        );
    }
}
