<?php

namespace App\Notifications\Contracts;

/**
 * Marks a notification that decides its own mail recipient.
 *
 * MailMessage has no ->to(). Illuminate\Notifications\Channels\MailChannel
 * reads the address exclusively from the notifiable's
 * routeNotificationFor('mail', $notification), so an appointment that should
 * go to patient_email rather than the account holder has to say so from here.
 *
 * User::routeNotificationForMail() checks for this interface.
 */
interface RoutesAppointmentMail
{
    /**
     * Address(es) this notification should be delivered to.
     *
     * @return array<string, string> [email => display name]; empty to suppress.
     */
    public function appointmentMailRecipient(object $notifiable): array;
}
