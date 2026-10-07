@use(App\Services\Settings\TenantSettings)
@php
    $settings = rescue(fn () => app(TenantSettings::class), null, false);
    $storeName = $settings?->store->name ?? 'KneadIt';
    $bakeryLogoUrl = rescue(fn () => $settings?->storeLogoUrl(), null, false);
@endphp

{{-- The bakery's own logo, sized by height in css/filament/admin/_chrome.css (.kn-brand). Only a bakery without a logo gets the KneadIt wordmark. --}}
@if ($bakeryLogoUrl)
    <span class="kn-brand">
        <img src="{{ $bakeryLogoUrl }}" alt="" class="kn-brand-logo" />
        <span class="kn-brand-name">{{ $storeName }}</span>
    </span>
@else
    <img src="{{ asset('images/logo-transparent.png') }}" alt="{{ $storeName }}" class="kn-brand-wordmark" />
@endif
