@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var \App\Models\Staff\User $user */
/** @var string $tenantId */
@endphp

Hi {{ $user->name }},

Your KneadIt free trial has expired. Your storefront has been paused.

Don't worry — your data is safe. Subscribe to reactivate:
https://getkneadit.app/billing/plans

Your admin panel is still accessible at:
https://{{ $tenantId }}.getkneadit.app/admin

— The KneadIt Team
