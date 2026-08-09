{{-- Edit the look of this email in resources/views/mail/smile.blade.php --}}
<x-mail::message>
# Your appointment has been moved

Hi {{ $patientName }},

Your appointment at {{ $clinicName }} has been rescheduled. The new details are below — please check they still work for you.

@include('emails.appointments._details')

@if ($ctaUrl)
<x-mail::button :url="$ctaUrl">
View Appointment
</x-mail::button>
@endif

<x-mail::subcopy>
@include('emails.appointments._contact', ['lead' => 'If you did not request this change, please let us know right away.'])
</x-mail::subcopy>
</x-mail::message>
