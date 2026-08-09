{{-- Edit the look of this email in resources/views/mail/smile.blade.php --}}
<x-mail::message>
# {{ $confirmed ? 'Your appointment is confirmed' : 'We received your booking' }}

Hi {{ $patientName }},

@if ($confirmed)
Good news — your appointment at {{ $clinicName }} has been confirmed. Here are the details:
@else
Thanks for booking with {{ $clinicName }}. Your request is in and our team is reviewing it — we will email you again as soon as it is confirmed.
@endif

@include('emails.appointments._details')

@if ($confirmed)
<x-mail::panel>
Please arrive 10 minutes early so we have time to check you in.
</x-mail::panel>
@endif

@if ($ctaUrl)
<x-mail::button :url="$ctaUrl">
View Appointment
</x-mail::button>
@endif

<x-mail::subcopy>
@include('emails.appointments._contact', ['lead' => 'Need to change or cancel?'])
</x-mail::subcopy>
</x-mail::message>
