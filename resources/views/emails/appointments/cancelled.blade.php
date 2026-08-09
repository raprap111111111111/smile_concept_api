{{-- Edit the look of this email in resources/views/mail/smile.blade.php --}}
<x-mail::message>
# Your appointment has been cancelled

Hi {{ $patientName }},

The following appointment at {{ $clinicName }} has been cancelled.

@include('emails.appointments._details')

@if ($reason)
<x-mail::panel>
**Reason:** {{ $reason }}
</x-mail::panel>
@endif

We would be glad to find you another time whenever you are ready.

@if ($clinicSiteUrl)
<x-mail::button :url="$clinicSiteUrl">
Book a New Appointment
</x-mail::button>
@endif

<x-mail::subcopy>
@include('emails.appointments._contact', ['lead' => 'Were you not expecting this? Please let us know so we can look into it.'])
</x-mail::subcopy>
</x-mail::message>
