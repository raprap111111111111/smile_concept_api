@props(['url'])
@php($logo = \App\Support\Mail\MailBranding::logoUrl())
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logo)
<img src="{{ $logo }}" class="logo" alt="{{ \App\Support\Mail\MailBranding::clinicName() }}">
@else
{{-- No usable logo URL: a styled wordmark beats a broken image. --}}
{!! $slot !!}
@endif
</a>
</td>
</tr>
