{{--
    Shared appointment details block, used by every appointment email.
    Change the date format or the rows here once and all four emails follow.

    Colours and spacing are NOT set here -- they live in
    resources/views/mail/smile.blade.php.
--}}
<x-mail::table>
| &nbsp; | &nbsp; |
| :--- | :--- |
| **When** | {{ $appointment->start_time?->format('l, F j, Y') }} at {{ $appointment->start_time?->format('g:i A') }} |
| **Doctor** | {{ $appointment->doctor?->user?->name ?? 'To be assigned' }} |
| **Branch** | {{ $appointment->branch?->name ?? '—' }} |
@if ($appointment->branch?->address)
| **Address** | {{ $appointment->branch->address }} |
@endif
@if ($appointment->reason_for_visit)
| **Reason for visit** | {{ $appointment->reason_for_visit }} |
@endif
</x-mail::table>
