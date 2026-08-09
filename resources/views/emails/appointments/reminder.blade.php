{{-- Edit the look of this email in resources/views/mail/smile.blade.php --}}
@php($soon = $hoursBefore <= 2)
<x-mail::message>
# {{ $soon ? 'Your appointment is coming up shortly' : 'A reminder about your appointment' }}

Hi {{ $patientName }},

This is a friendly reminder about your upcoming appointment at {{ $clinicName }}.

@include('emails.appointments._details')

<x-mail::panel>
Please arrive 10 minutes early so we have time to check you in.
</x-mail::panel>

@if ($ctaUrl)
<x-mail::button :url="$ctaUrl">
View Appointment
</x-mail::button>
@endif

<x-mail::subcopy>
@include('emails.appointments._contact', ['lead' => 'Need to reschedule? Let us know as soon as you can so we can offer the slot to someone else.'])
</x-mail::subcopy>
</x-mail::message>
