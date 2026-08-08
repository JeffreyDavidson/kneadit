@use(App\Services\Settings\TenantSettings)
@php
    $settings = rescue(fn () => app(TenantSettings::class), null, false);
    $storeName = $settings?->storeName ?? 'KneadIt';
    // Prefer the bakery's own logo when one's set; otherwise show the KneadIt
    // platform logo so the sidebar always renders an image, not text.
    $logoUrl = rescue(fn () => $settings?->storeLogoUrl(), null, false) ?? asset('images/logo-transparent.png');
@endphp

<img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="h-9 w-auto" />
