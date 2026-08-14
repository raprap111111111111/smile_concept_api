{{-- Edit the look of this email in resources/views/mail/smile.blade.php --}}
<x-mail::message>
# How was your visit?

Hi {{ $patientName }},

Thank you for visiting {{ $clinicName }}. We hope everything went smoothly and you are feeling well.

@include('emails.appointments._details')

<x-mail::panel>
A few aftercare reminders:

- Avoid very hot or very cold food for the rest of the day if you had dental work done.
- Mild sensitivity is normal for a day or two.
- If you experience persistent pain, swelling, or bleeding, contact us right away.
</x-mail::panel>

@if ($ctaUrl)
<x-mail::button :url="$ctaUrl">
View Appointment
</x-mail::button>
@endif

<x-mail::subcopy>
@include('emails.appointments._contact', ['lead' => 'Questions about your treatment?'])
</x-mail::subcopy>
</x-mail::message>
