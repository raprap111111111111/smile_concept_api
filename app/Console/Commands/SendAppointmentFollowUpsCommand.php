<?php

namespace App\Console\Commands;

use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\AppointmentFollowUpNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentFollowUpsCommand extends Command
{
    protected $signature = 'appointments:send-followups
                            {--limit=100 : Max follow-ups per run}
                            {--dry-run : List what would be sent without sending}';

    protected $description = 'Send the aftercare follow-up email for completed appointments';

    public function handle(AppointmentSettings $settings): int
    {
        if (!$settings->sendFollowUpEmail) {
            $this->info('Follow-up emails are disabled (send_followup_email = false). Nothing to do.');
            return Command::SUCCESS;
        }

        $threshold = Carbon::now()->subHours($settings->followUpHoursAfter);

        $due = Appointment::query()
            ->where('status', AppointmentStatus::COMPLETED->value)
            ->whereNull('followup_sent_at')
            ->where('end_time', '<=', $threshold)
            ->orderBy('end_time')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($due->isEmpty()) {
            $this->info('No follow-ups due.');
            return Command::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sent   = 0;

        foreach ($due as $appointment) {
            $label = sprintf(
                '#%d %s (ended %s)',
                $appointment->id,
                $appointment->notificationName() ?? 'unknown patient',
                $appointment->end_time?->toDateTimeString() ?? '?',
            );

            if ($dryRun) {
                $this->line("[dry-run] would send follow-up for {$label}");
                continue;
            }

            // Stamp BEFORE notifying: the notification is queued, and a crash
            // between notify() and save() must not double-send on the next run.
            $appointment->forceFill(['followup_sent_at' => Carbon::now()])->save();

            $appointment->user?->notify(new AppointmentFollowUpNotification($appointment));

            $this->line("Sent follow-up for {$label}");
            $sent++;
        }

        $this->info($dryRun
            ? "Dry run complete: {$due->count()} follow-up(s) due."
            : "Sent {$sent} follow-up(s).");

        return Command::SUCCESS;
    }
}
