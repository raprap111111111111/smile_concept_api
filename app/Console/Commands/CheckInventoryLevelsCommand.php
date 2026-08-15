<?php

namespace App\Console\Commands;

use App\Domain\Inventories\DTOs\InventorySettings;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\User;
use App\Notifications\StockAlertDigestNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class CheckInventoryLevelsCommand extends Command
{
    protected $signature = 'inventory:check-levels
                            {--dry-run : List what would be sent without sending}
                            {--cooldown= : Override the configured cooldown, in days}
                            {--force : Ignore the configured send hour and run now}';

    protected $description = 'Send each branch a digest of low stock and expiring batches';

    public function handle(InventorySettings $settings): int
    {
        if (! $settings->lowStockAlertsEnabled) {
            $this->info('Low-stock alerts are disabled (inventory_low_stock_alert_enabled = false). Nothing to do.');

            return Command::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        // The scheduler builds its cron expressions at boot from static config,
        // so an admin-tunable hour cannot drive dailyAt(). The command runs
        // hourly instead and decides for itself whether this is the hour.
        if (! $dryRun && ! $this->option('force') && Carbon::now()->hour !== $settings->lowStockAlertHour) {
            return Command::SUCCESS;
        }

        $cooldownDays = $this->option('cooldown') !== null
            ? max(0, (int) $this->option('cooldown'))
            : $settings->lowStockCooldownDays;

        $cooldownCutoff = Carbon::now()->subDays($cooldownDays);
        $expiryCutoff   = Carbon::now()->addDays($settings->expiryWarningDays);

        $branches = Branch::query()->orderBy('id')->get();
        $sent = 0;

        foreach ($branches as $branch) {
            $lowStock = $this->lowStockAt($branch->id, $cooldownDays, $cooldownCutoff);
            $expiring = $this->expiringAt($branch->id, $expiryCutoff);

            if ($lowStock->isEmpty() && $expiring->isEmpty()) {
                continue;
            }

            $recipients = $this->stockWatchersAt($branch->id);

            if ($recipients->isEmpty()) {
                $this->warn("No one at {$branch->name} can see stock — skipping its digest.");
                continue;
            }

            $lowStockPayload = $lowStock->map(fn (Inventory $row): array => [
                'item_id'   => (int) $row->item_id,
                'name'      => (string) ($row->item?->name ?? 'Unknown item'),
                'sku'       => (string) ($row->item?->sku ?? ''),
                'quantity'  => (int) $row->quantity,
                'threshold' => (int) ($row->item?->minimum_threshold ?? $settings->defaultMinimumThreshold),
                'unit'      => (string) ($row->item?->unit_of_measure ?? 'unit'),
            ])->values()->all();

            $expiringPayload = $expiring->map(fn (InventoryBatch $batch): array => [
                'batch_id'    => (int) $batch->id,
                'item_id'     => (int) $batch->item_id,
                'name'        => (string) ($batch->item?->name ?? 'Unknown item'),
                'lot_number'  => $batch->lot_number,
                'expiry_date' => $batch->expiry_date?->toDateString() ?? '',
                'quantity'    => (int) $batch->quantity_remaining,
                'days_left'   => (int) Carbon::now()->startOfDay()->diffInDays($batch->expiry_date, false),
            ])->values()->all();

            if ($dryRun) {
                $this->line(sprintf(
                    '[dry-run] %s: %d low, %d expiring, to %d recipient(s)',
                    $branch->name,
                    count($lowStockPayload),
                    count($expiringPayload),
                    $recipients->count(),
                ));
                continue;
            }

            // Stamp BEFORE notifying. Notifications are queued, and a crash
            // between the send and the save would re-alert every run — the same
            // reasoning as SendAppointmentFollowUpsCommand.
            if ($lowStock->isNotEmpty()) {
                Inventory::whereIn('id', $lowStock->pluck('id'))
                    ->update(['last_low_stock_alert_at' => Carbon::now()]);
            }

            Notification::send(
                $recipients,
                new StockAlertDigestNotification($branch, $lowStockPayload, $expiringPayload),
            );

            $this->line(sprintf(
                '%s: notified %d recipient(s) — %d low, %d expiring',
                $branch->name,
                $recipients->count(),
                count($lowStockPayload),
                count($expiringPayload),
            ));

            $sent++;
        }

        $this->info($dryRun
            ? 'Dry run complete.'
            : "Sent {$sent} digest(s).");

        return Command::SUCCESS;
    }

    /**
     * Items at or below their reorder point, skipping anything alerted on
     * inside the cooldown.
     *
     * @return Collection<int, Inventory>
     */
    private function lowStockAt(int $branchId, int $cooldownDays, Carbon $cutoff): Collection
    {
        return Inventory::query()
            ->with('item')
            ->where('branch_id', $branchId)
            ->whereHas('item', fn ($q) => $q->whereColumn(
                'items.minimum_threshold', '>=', 'inventories.quantity'
            ))
            ->when($cooldownDays > 0, fn ($q) => $q->where(
                fn ($inner) => $inner
                    ->whereNull('last_low_stock_alert_at')
                    ->orWhere('last_low_stock_alert_at', '<', $cutoff)
            ))
            ->get();
    }

    /**
     * Batches with stock left that expire inside the warning window.
     *
     * Already-expired lots are included: they are the most urgent thing on the
     * shelf and must not silently drop off the list once the date passes.
     *
     * @return Collection<int, InventoryBatch>
     */
    private function expiringAt(int $branchId, Carbon $cutoff): Collection
    {
        return InventoryBatch::query()
            ->with('item')
            ->where('branch_id', $branchId)
            ->open()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $cutoff)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Staff at this branch who can see stock, so the digest reaches someone who
     * can restock rather than every admin in the system.
     *
     * @return Collection<int, User>
     */
    private function stockWatchersAt(int $branchId): Collection
    {
        return User::query()
            ->where(fn ($query) => $query
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
                ->orWhere('branch_id', $branchId))
            ->get()
            ->filter(fn (User $user): bool => $user->can('inventory.viewAny'))
            ->values();
    }
}
