@php($branding = \App\Support\Mail\MailBranding::class)
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="$branding::siteUrl()">
{{ $branding::clinicName() }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
{{-- Only the copyright line lives here; the clinic's contact details and the
     do-not-reply notice are rendered by the footer component itself. --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $branding::clinicName() }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
