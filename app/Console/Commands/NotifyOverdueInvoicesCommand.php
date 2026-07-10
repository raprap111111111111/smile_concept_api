<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueInvoicesCommand extends Command
{
    protected $signature = 'invoices:notify-overdue
                            {--dry-run : Preview without sending}
                            {--cooldown=7 : Days to wait before re-notifying}';

    protected $description = 'Send overdue notifications for unpaid or partially-paid invoices';

    public function handle(): int
    {
        $dryRun   = (bool) $this->option('dry-run');
        $cooldown = (int)  $this->option('cooldown');

        // ─── 1. Query eligible overdue invoices ───────────────
        $overdue = Invoice::query()
            ->with(['appointment.user'])
            ->whereIn('status', [
                InvoiceStatus::UNPAID->value,
                InvoiceStatus::PARTIAL->value,
            ])
            ->whereDate('due_date', '<', now())
            ->where(function ($q) use ($cooldown) {
                $q->whereNull('last_overdue_notification_at')
                  ->orWhere('last_overdue_notification_at', '<', now()->subDays($cooldown));
            })
            ->get();

        if ($overdue->isEmpty()) {
            $this->info('✅ No overdue invoices to notify.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdue->count()} overdue invoice(s) eligible for notification.");

        // ─── 2. Dry-run mode (preview) ────────────────────────
        if ($dryRun) {
            $this->warn('🧪 DRY RUN — no notifications will be sent.');
            $this->table(
                ['Invoice #', 'Patient', 'Balance', 'Days Overdue', 'Last Notified'],
                $overdue->map(fn($inv) => [
                    $inv->invoice_number,
                    $inv->appointment?->user?->name ?? 'N/A',
                    '₱' . number_format((float) $inv->balance_due, 2),
                    (int) abs(now()->diffInDays($inv->due_date)) . ' days',
                    $inv->last_overdue_notification_at?->diffForHumans() ?? 'Never',
                ])
            );
            return Command::SUCCESS;
        }

        // ─── 3. Get admins once ───────────────────────────────
        $admins        = $this->getAdmins();
        $notifiedCount = 0;
        $skippedCount  = 0;

        // ─── 4. Send notifications ────────────────────────────
        foreach ($overdue as $invoice) {
            $patient = $invoice->appointment?->user;

            if (!$patient) {
                $this->warn("⚠️  Skipped {$invoice->invoice_number} — no patient linked.");
                $skippedCount++;
                continue;
            }

            $notification = new InvoiceOverdueNotification($invoice);

            // Notify patient (email + bell)
            $patient->notify($notification);

            // Notify admins (bell only for tracking)
            if ($admins->isNotEmpty()) {
                Notification::send($admins, $notification);
            }

            // ✅ Save timestamp to enforce cooldown
            $invoice->update(['last_overdue_notification_at' => now()]);

            $notifiedCount++;
        }

        $this->info("✅ Sent {$notifiedCount} overdue notification(s).");

        if ($skippedCount > 0) {
            $this->warn("⚠️  Skipped {$skippedCount} invoice(s) with no patient.");
        }

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, User>
     */
    private function getAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->get();
    }
}