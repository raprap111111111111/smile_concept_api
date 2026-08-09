@php
    $clinic  = \App\Support\Mail\MailBranding::clinicName();
    $address = \App\Support\Mail\MailBranding::address();
    $phone   = \App\Support\Mail\MailBranding::phone();
    $email   = \App\Support\Mail\MailBranding::email();
@endphp
--
{{ $clinic }}
@if ($address){{ $address }}
@endif
@if ($phone)Phone: {{ $phone }}
@endif
@if ($email)Email: {{ $email }}
@endif

This is an automated message, so please do not reply to it.@if ($phone) To change or cancel an appointment, call us on {{ $phone }}.@endif

{{ $slot }}
