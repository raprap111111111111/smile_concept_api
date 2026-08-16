<?php

namespace App\Notifications;

use App\Models\TreatmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Raised when supplies recorded against a completed treatment plan exceeded
 * what the branch had on record.
 *
 * The recording is never blocked, so this is how anyone finds out. The branch
 * travels explicitly because plans, unlike appointments, belong to no branch —
 * staff choose one when they record.
 */
class TreatmentPlanStockShortfallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param list<array{item_id:int,name:string,sku:string,short_by:int,unit:string}> $items
     */
    public function __construct(
        public readonly TreatmentPlan $plan,
        public readonly int $branchId,
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
            'title'             => 'Stock ran short',
            'message'           => $this->summary(),
            'treatment_plan_id' => $this->plan->id,
            'branch_id'         => $this->branchId,
            'items'             => $this->items,
            'action_url'        => '/inventory',
            'icon'              => 'inventory_2',
            'color'             => 'warning',
        ];
    }

    private function summary(): string
    {
        $count = count($this->items);

        if ($count === 1) {
            $item = $this->items[0];

            return sprintf(
                'Treatment plan "%s" used %d more %s than %s had in stock. The balance is now negative.',
                $this->plan->name,
                $item['short_by'],
                $item['unit'] ?: 'units',
                $item['name'],
            );
        }

        return sprintf(
            'Treatment plan "%s" ran short on %d supplies. Their balances are now negative.',
            $this->plan->name,
            $count,
        );
    }
}
