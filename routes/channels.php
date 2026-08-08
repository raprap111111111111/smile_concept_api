<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| /broadcasting/auth is registered in bootstrap/app.php via
| ->withBroadcasting(..., attributes: ['middleware' => ['auth:api']]), so $user
| here is always resolved from a Passport bearer token. The ['guards' => ['api']]
| third argument is belt-and-braces for the same thing (Broadcaster::retrieveUser).
|
| Channel names below are unprefixed. Clients subscribe to the wire name with the
| "private-" prefix — private-clinic.appointments, not clinic.appointments.
| Registering the prefixed name here is a guaranteed 403.
|
*/

// Per-user channel: notification bell, plus a patient's own appointments.
// Name matches Laravel's own Channel::name() convention for a User model, so
// broadcastOn() can just return new PrivateChannel('App.Models.User.'.$id).
Broadcast::channel('App.Models.User.{userId}', function (User $user, int $userId): bool {
    return (int) $user->getKey() === $userId;
}, ['guards' => ['api']]);

// Shared staff feed for appointment create/status changes.
Broadcast::channel('clinic.appointments', function (User $user): bool {
    return $user->isStaff();
}, ['guards' => ['api']]);

// Dashboard counters. Mirrors the HTTP gate on routes/api/dashboard.php, which
// groups the dashboard endpoints behind `permission:dashboard.view`.
// Gate::before in AppServiceProvider gives super-admin the bypass.
Broadcast::channel('clinic.dashboard', function (User $user): bool {
    return $user->can('dashboard.view');
}, ['guards' => ['api']]);
