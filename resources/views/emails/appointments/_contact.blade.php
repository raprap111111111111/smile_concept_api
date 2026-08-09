{{--
    Builds the "how to reach us" phrase used in each email's subcopy, so the
    templates never have to nest @if directives mid-sentence. Blade only
    compiles a directive when it is not preceded by a word character, so
    "...right away@else" silently fails to compile -- keep directives at the
    start of a line, or precompute the sentence like this.

    Expects: $lead (e.g. "Need to change or cancel?")
--}}
@php
    $phone   = \App\Support\Mail\MailBranding::phone();
    $mailbox = \App\Support\Mail\MailBranding::email();

    $how = match (true) {
        $phone && $mailbox => "call us on {$phone} or email {$mailbox}",
        (bool) $phone      => "call us on {$phone}",
        (bool) $mailbox    => "email us at {$mailbox}",
        default            => 'get in touch with the clinic',
    };
@endphp
{{ $lead }} Just {{ $how }}.
