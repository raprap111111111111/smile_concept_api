<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use App\Support\Mail\MailBranding;

/**
 * Shared channel selection, recipient routing and view data for the
 * appointment notifications.
 *
 * All four of them are sent to two different audiences from the same dispatch
 * sites -- the patient, and every admin/owner via Notification::send($admins).
 * Only the patient gets email; admins stay bell-only. That rule lives here so
 * it cannot drift between the four classes.
 *
 * @property-read \App\Models\Appointment $appointment
 */
trait NotifiesAppointmentPatient
{
    /**
     * Is this notifiable the patient the appointment belongs to?
     */
    protected function isAppointmentPatient(object $notifiable): bool
    {
        return $notifiable instanceof User
            && (int) $notifiable->getKey() === (int) $this->appointment->user_id;
    }

    /**
     * Channels for this notifiable.
     *
     * The bell always fires. Email is added only when all of these hold:
     *   - the notifiable is the patient (never an admin),
     *   - the clinic has not switched email off in Settings,
     *   - we actually have a usable address to send to.
     *
     * @return array<int, string>
     */
    protected function patientChannels(object $notifiable, bool $wantsMail = true): array
    {
        $channels = ['database'];

        if ($wantsMail
            && $this->isAppointmentPatient($notifiable)
            && (bool) setting('email_enabled', true)
            && $this->appointment->notificationEmail() !== null
        ) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<string, string>
     */
    public function appointmentMailRecipient(object $notifiable): array
    {
        if ($this->isAppointmentPatient($notifiable)
            && ($email = $this->appointment->notificationEmail()) !== null
        ) {
            return [$email => $this->appointment->notificationName()];
        }

        // Anyone else (an admin, if patientChannels() is ever bypassed) gets
        // their own address -- never the patient's.
        $own = trim((string) ($notifiable->email ?? ''));

        return $own === '' ? [] : [$own => (string) ($notifiable->name ?? '')];
    }

    /**
     * View data shared by every appointment email.
     *
     * Do not add keys named level, subject, greeting, introLines, outroLines,
     * actionText, actionUrl, displayableActionUrl or color -- MailChannel
     * merges MailMessage::data() over this array and they would collide.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function appointmentMailData(array $extra = []): array
    {
        return array_merge([
            'appointment' => $this->appointment,
            'patientName' => $this->appointment->notificationName(),
            'ctaUrl'      => $this->appointmentUrl(),
        ], MailBranding::toArray(), $extra);
    }

    /**
     * Deep link to the appointment, or null when no client URL is configured.
     *
     * This API serves no patient-facing pages, so url('/appointments/5') would
     * be a guaranteed 404 in the inbox. Templates omit the button entirely
     * rather than ship a dead link.
     */
    protected function appointmentUrl(): ?string
    {
        $base = trim((string) config('app.frontend_url'));

        return $base === ''
            ? null
            : rtrim($base, '/') . '/appointments/' . $this->appointment->id;
    }
}
