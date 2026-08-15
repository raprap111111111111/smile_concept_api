<?php

namespace App\Notifications;

use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * One digest per branch covering both things worth acting on: supplies at or
 * below their reorder point, and batches about to expire.
 *
 * Deliberately one notification rather than two streams. Both answer the same
 * question — "what do I need to deal with in the stock cupboard" — and splitting
 * them would double the noise for the same information.
 */
class StockAlertDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param list<array{item_id:int,name:string,sku:string,quantity:int,threshold:int,unit:string}> $lowStock
     * @param list<array{batch_id:int,item_id:int,name:string,lot_number:?string,expiry_date:string,quantity:int,days_left:int}> $expiring
     */
    public function __construct(
        public readonly Branch $branch,
        public readonly array $lowStock,
        public readonly array $expiring,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Stock needs attention',
            'message'    => $this->summary(),
            'branch_id'  => $this->branch->id,
            'low_stock'  => $this->lowStock,
            'expiring'   => $this->expiring,
            'action_url' => '/inventory',
            'icon'       => 'inventory_2',
            'color'      => 'warning',
        ];
    }

    private function summary(): string
    {
        $parts = [];

        if ($this->lowStock !== []) {
            $count = count($this->lowStock);
            $parts[] = $count === 1
                ? '1 item is at or below its reorder point'
                : "{$count} items are at or below their reorder point";
        }

        if ($this->expiring !== []) {
            $count = count($this->expiring);
            $parts[] = $count === 1
                ? '1 batch is expiring soon'
                : "{$count} batches are expiring soon";
        }

        return sprintf('%s at %s.', implode(' and ', $parts), $this->branch->name);
    }
}
