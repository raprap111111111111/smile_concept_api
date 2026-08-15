<?php

namespace App\Events\Dashboard;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Something happened that makes one or more dashboard panels out of date.
 *
 * Carries no counter values on purpose. DashboardMetricsService::getStats()
 * returns eleven keys including three trend arrays; recomputing all of that on
 * every mutation would be both expensive and a second source of truth that could
 * disagree with the HTTP endpoint. The client refetches the named scopes
 * instead, which keeps this event cheap and unambiguous.
 */
class DashboardCountersStale implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Stock levels. Unlike the three below it this has no dashboard endpoint of
     * its own yet — it is dispatched when a shortfall is recorded so a client
     * showing stock can refetch without polling.
     */
    public const SCOPE_INVENTORY = 'inventory';

    public const SCOPE_STATS = 'stats';
    public const SCOPE_SCHEDULE = 'schedule';
    public const SCOPE_ACTIVITY = 'activity';

    /**
     * @param  array<int, string>  $scopes  Which panels to refetch.
     * @param  string  $reason  Diagnostic only — lets you see in the client log
     *                          which mutation caused a refetch.
     */
    public function __construct(
        public array $scopes,
        public string $reason,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('clinic.dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.stale';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'scopes' => $this->scopes,
            'reason' => $this->reason,
        ];
    }
}
