<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? $ogStoreName }}</title>
    <meta name="description" content="{{ $metaDescription ?? $ogDescription }}" />
    <meta property="og:title" content="{{ $title ?? $ogStoreName }}" />
    <meta property="og:description" content="{{ $metaDescription ?? $ogDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    @if ($ogLogo)
        <meta property="og:image" content="{{ $ogLogo }}" />
    @endif
    <meta property="og:site_name" content="{{ $ogStoreName }}" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="{{ $title ?? $ogStoreName }}" />
    <meta name="twitter:description" content="{{ $metaDescription ?? $ogDescription }}" />
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Dancing+Script:wght@400;600;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap"
        rel="stylesheet"
    />

    <x-layouts.storefront-styles />

    <link rel="manifest" href="/manifest.json" />
    <meta name="theme-color" content="{{ tenant()->brand_color_primary ?? '#d4920c' }}" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <link rel="apple-touch-icon" href="/icons/icon-192.png" />
    @if ($settings->store->logo)
        <link rel="icon" href="{{ asset('storage/' . $settings->store->logo) }}" type="image/png" />
    @else
        <link
            rel="icon"
            href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='{{ urlencode(tenant()->brand_color_primary ?? '#d4920c') }}'/><text x='16' y='22' text-anchor='middle' fill='white' font-size='18' font-family='serif' font-weight='bold'>{{ substr($settings->store->name, 0, 1) }}</text></svg>"
            type="image/svg+xml"
        />
        @endif
        {{ $styles ?? "" }}
        <x-analytics.fathom />
</head>
<body data-theme="{{ $storefrontTheme }}" {{ $bodyAttrs ?? "" }}>
    @php
        $storeName = $settings->store->name;
        $cateringEnabled = $settings->catering->enabled;
        $loyaltyEnabled = $settings->loyalty->enabled;
        $loyaltyName = $settings->loyalty->programName;
        $exploreActive = request()->routeIs('storefront.blog*', 'storefront.gallery', 'storefront.reviews', 'storefront.about', 'storefront.catering');
        $accountActive = request()->routeIs('order.track', 'storefront.giftCards', 'storefront.rewards');
    @endphp

    <x-storefront.navigation
        :storefront-theme="$storefrontTheme"
        :store-name="$storeName"
        :catering-enabled="$cateringEnabled"
        :loyalty-enabled="$loyaltyEnabled"
        :loyalty-name="$loyaltyName"
        :explore-active="$exploreActive"
        :account-active="$accountActive"
    />
    @php
        $pageTestId = 'page-'.str_replace(['storefront.', '.'], ['', '-'], request()->route()?->getName() ?? 'unknown');
    @endphp
    <main @class(['min-h-screen', 'pt-24' => $storefrontTheme !== 'biscotto']) data-test="{{ $pageTestId }}">
        <x-storefront.announcement :settings="$settings" />

        {{ $slot }}
    </main>

    <x-storefront.policies :settings="$settings" />
    <x-storefront.footer :settings="$settings" />
    <x-storefront.pwa-install-prompt />
    <x-storefront.cookie-consent />
    {{ $scripts ?? "" }}
</body>
</html>
