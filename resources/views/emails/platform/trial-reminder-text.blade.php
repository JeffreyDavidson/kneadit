@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var \App\Models\Staff\User $user */
/** @var int $daysLeft */
/** @var string $billingPlansUrl */
@endphp

Hi {{ $user->name }},

Your KneadIt free trial for {{ $storeName }} ends {{ $daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days" }}.

Subscribe now to keep your bakery running without interruption:
{{ $billingPlansUrl }}
@if ($daysLeft <= 3)

After your trial expires, your storefront will be paused until you subscribe.
@endif

Questions? Just reply to this email.

— The KneadIt Team
