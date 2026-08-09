@php
    $clinic  = \App\Support\Mail\MailBranding::clinicName();
    $address = \App\Support\Mail\MailBranding::address();
    $phone   = \App\Support\Mail\MailBranding::phone();
    $email   = \App\Support\Mail\MailBranding::email();
@endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<p>
<strong>{{ $clinic }}</strong>@if ($address)<br>{{ $address }}@endif
@if ($phone || $email)
<br>
@if ($phone){{ $phone }}@endif
@if ($phone && $email) &middot; @endif
@if ($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@endif
@endif
</p>
<p>This is an automated message, so please do not reply to it.@if ($phone) To change or cancel an appointment, call us on {{ $phone }}.@endif</p>
{{-- The copyright line comes in via the slot from message.blade.php. --}}
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
