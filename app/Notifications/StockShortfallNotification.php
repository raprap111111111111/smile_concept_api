<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Raised when a completed appointment consumed more of something than the
 * branch had on record.
 *
 * The appointment is never blocked, so this is how anyone finds out. It is
 * addressed to staff at that branch who can see stock, rather than to every
 * admin — the point is to reach someone who can restock the cupboard.
 */
class StockShortfallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param list<array{item_id:int,name:string,sku:string,short_by:int,unit:string}> $items
     */
    public function __construct(
        public readonly Appointment $appointment,
        public readonly array $items,
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
            'title'          => 'Stock ran short',
            'message'        => $this->summary(),
            'appointment_id' => $this->appointment->id,
            'branch_id'      => $this->appointment->branch_id,
            'items'          => $this->items,
            'action_url'     => '/inventory',
            'icon'           => 'inventory_2',
            'color'          => 'warning',
        ];
    }

    private function summary(): string
    {
        $count = count($this->items);

        if ($count === 1) {
            $item = $this->items[0];

            return sprintf(
                'Appointment #%d used %d more %s than %s had in stock. The balance is now negative.',
                $this->appointment->id,
                $item['short_by'],
                $item['unit'] ?: 'units',
                $item['name'],
            );
        }

        return sprintf(
            'Appointment #%d ran short on %d supplies. Their balances are now negative.',
            $this->appointment->id,
            $count,
        );
    }
}
