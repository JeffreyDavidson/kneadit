<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? $ogStoreName }}</title>
    <meta name="description" content="{{ $metaDescription ?? $ogDescription }}">
    <meta property="og:title" content="{{ $title ?? $ogStoreName }}">
    <meta property="og:description" content="{{ $metaDescription ?? $ogDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogLogo)
    <meta property="og:image" content="{{ $ogLogo }}">
    @endif
    <meta property="og:site_name" content="{{ $ogStoreName }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? $ogStoreName }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? $ogDescription }}">
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Dancing+Script:wght@400;600;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">


    @include('components.layouts.storefront-styles')
    {{-- Original style block replaced by include above --}}

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="{{ tenant()->brand_color_primary ?? '#d4920c' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    @if ($settings->storeLogo)
    <link rel="icon" href="{{ asset('storage/' . $settings->storeLogo) }}" type="image/png">
    @else
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='{{ urlencode(tenant()->brand_color_primary ?? '#d4920c') }}'/><text x='16' y='22' text-anchor='middle' fill='white' font-size='18' font-family='serif' font-weight='bold'>{{ substr($settings->storeName, 0, 1) }}</text></svg>" type="image/svg+xml">
    @endif

        {{ $styles ?? "" }}\n
@include('partials.fathom')
</head>
<body data-theme="{{ $storefrontTheme }}" {{ $bodyAttrs ?? "" }}>

    @php
        $storeName = $settings->storeName;
        $cateringEnabled = $settings->cateringEnabled;
        $loyaltyEnabled = $settings->loyaltyEnabled;
        $loyaltyName = $settings->loyaltyProgramName;
        $exploreActive = request()->routeIs('storefront.blog*', 'storefront.gallery', 'storefront.reviews', 'storefront.about', 'storefront.catering');
        $accountActive = request()->routeIs('order.track', 'storefront.giftCards', 'storefront.rewards');
    @endphp

    <!-- Navigation -->
    <nav class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50" style="width: max-content; max-width: 95vw;">
        <div style="background: var(--warm-800); border: 1px solid rgba(139, 104, 68, 0.2);" class="backdrop-blur-sm rounded-full px-6 py-3">

            <!-- Desktop Nav -->
            <div class="nav-desktop items-center space-x-1" x-data="{ explore: false, account: false }" @click.outside="explore = false; account = false">
                <a href="{{ url('/') }}" class="nav-link font-script" style="color: var(--warm-400); font-size: 1.15rem; padding: 8px 16px;">
                    {{ $storeName }}
                </a>
                <span style="color: var(--warm-600); opacity: 0.3;">|</span>

                <a href="{{ route('storefront.menu') }}" class="nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}">Menu</a>
                <a href="{{ route('order.create') }}" class="nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}">Order</a>

                <!-- Explore Dropdown -->
                <div class="relative">
                    <button @click="explore = !explore; account = false"
                            class="nav-link font-display inline-flex items-center gap-1 {{ $exploreActive ? 'active' : '' }}">
                        Explore
                        <svg class="w-3.5 h-3.5 transition-transform" :class="explore ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="explore" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-3 py-2 min-w-[180px] rounded-xl shadow-xl"
                         style="background: var(--warm-800); border: 1px solid rgba(139, 104, 68, 0.25);">
                        <a href="{{ route('storefront.blog') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.blog*') ? 'active' : '' }}">Blog</a>
                        <a href="{{ route('storefront.gallery') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.gallery') ? 'active' : '' }}">Gallery</a>
                        <a href="{{ route('storefront.reviews') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.reviews') ? 'active' : '' }}">Reviews</a>
                        <a href="{{ route('storefront.about') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.about') ? 'active' : '' }}">About</a>
                        @if ($cateringEnabled)
                        <a href="{{ route('storefront.catering') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.catering') ? 'active' : '' }}">Catering</a>
                        @endif
                    </div>
                </div>

                <!-- My Account Dropdown -->
                <div class="relative">
                    <button @click="account = !account; explore = false"
                            class="nav-link font-display inline-flex items-center gap-1 {{ $accountActive ? 'active' : '' }}">
                        My Account
                        <svg class="w-3.5 h-3.5 transition-transform" :class="account ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="account" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-3 py-2 min-w-[180px] rounded-xl shadow-xl"
                         style="background: var(--warm-800); border: 1px solid rgba(139, 104, 68, 0.25);">
                        <a href="{{ route('order.track') }}" class="nav-dropdown-link font-display {{ request()->routeIs('order.track') ? 'active' : '' }}">Track Order</a>
                        <a href="{{ route('storefront.giftCards') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.giftCards') ? 'active' : '' }}">Gift Cards</a>
                        @if ($loyaltyEnabled)
                        <a href="{{ route('storefront.rewards') }}" class="nav-dropdown-link font-display {{ request()->routeIs('storefront.rewards') ? 'active' : '' }}">{{ $loyaltyName }}</a>
                        @endif
                    </div>
                </div>

                <a href="{{ route('contact.show') }}" class="nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}">Contact</a>
            </div>

            <!-- Mobile Nav -->
            <div class="nav-mobile" x-data="{ open: false, explore: false, account: false }">
                <div class="flex items-center justify-between">
                    <a href="{{ url('/') }}" class="font-script text-lg" style="color: var(--warm-400); text-decoration: none;">
                        {{ $storeName }}
                    </a>
                    <button @click="open = !open" class="nav-link font-display ml-4" style="padding: 8px 12px;">
                        <span x-text="open ? '✕' : '☰'"></span>
                    </button>
                </div>
                <div x-show="open" x-collapse class="mt-4 space-y-1">
                    <a href="{{ route('storefront.menu') }}" class="block nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}">Menu</a>
                    <a href="{{ route('order.create') }}" class="block nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}">Order</a>

                    <!-- Mobile Explore Group -->
                    <button @click="explore = !explore" class="w-full text-left nav-link font-display inline-flex items-center justify-between {{ $exploreActive ? 'active' : '' }}">
                        Explore
                        <svg class="w-3.5 h-3.5 transition-transform" :class="explore ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="explore" x-collapse class="pl-4 space-y-1">
                        <a href="{{ route('storefront.blog') }}" class="block nav-link font-display {{ request()->routeIs('storefront.blog*') ? 'active' : '' }}">Blog</a>
                        <a href="{{ route('storefront.gallery') }}" class="block nav-link font-display {{ request()->routeIs('storefront.gallery') ? 'active' : '' }}">Gallery</a>
                        <a href="{{ route('storefront.reviews') }}" class="block nav-link font-display {{ request()->routeIs('storefront.reviews') ? 'active' : '' }}">Reviews</a>
                        <a href="{{ route('storefront.about') }}" class="block nav-link font-display {{ request()->routeIs('storefront.about') ? 'active' : '' }}">About</a>
                        @if ($cateringEnabled)
                        <a href="{{ route('storefront.catering') }}" class="block nav-link font-display {{ request()->routeIs('storefront.catering') ? 'active' : '' }}">Catering</a>
                        @endif
                    </div>

                    <!-- Mobile Account Group -->
                    <button @click="account = !account" class="w-full text-left nav-link font-display inline-flex items-center justify-between {{ $accountActive ? 'active' : '' }}">
                        My Account
                        <svg class="w-3.5 h-3.5 transition-transform" :class="account ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="account" x-collapse class="pl-4 space-y-1">
                        <a href="{{ route('order.track') }}" class="block nav-link font-display {{ request()->routeIs('order.track') ? 'active' : '' }}">Track Order</a>
                        <a href="{{ route('storefront.giftCards') }}" class="block nav-link font-display {{ request()->routeIs('storefront.giftCards') ? 'active' : '' }}">Gift Cards</a>
                        @if ($loyaltyEnabled)
                        <a href="{{ route('storefront.rewards') }}" class="block nav-link font-display {{ request()->routeIs('storefront.rewards') ? 'active' : '' }}">{{ $loyaltyName }}</a>
                        @endif
                    </div>

                    <a href="{{ route('contact.show') }}" class="block nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen pt-24">
        @php
            $announcementEnabled = $settings->announcementEnabled ? '1' : '0';
            $announcementText = $settings->announcementText;
            $announcementType = $settings->announcementType;
            $announcementDismissKey = $announcementText
                ? 'announcement_dismissed_' . hash('xxh128', $announcementText)
                : null;
        @endphp

        @if ($announcementEnabled === '1' && $announcementDismissKey)
        <div x-data="{ show: !localStorage.getItem('{{ $announcementDismissKey }}') }"
             x-show="show"
             x-transition
             class="relative px-4 py-3 text-center text-sm font-medium"
             :style="show ? '' : 'display:none'"
             style="
                @if ($announcementType === 'warning')
                    background: #fef3cd; color: #856404; border-bottom: 2px solid #ffc107;
                @elseif ($announcementType === 'success')
                    background: #d4edda; color: #155724; border-bottom: 2px solid #28a745;
                @elseif ($announcementType === 'holiday')
                    background: linear-gradient(135deg, #c41e3a, #1a6b2a); color: #fff; border-bottom: 2px solid #ffd700;
                @else
                    background: #fff3cd; color: #664d03; border-bottom: 2px solid var(--warm-500);
                @endif
             ">
            <span>{{ $announcementText }}</span>
            <button @click="show = false; localStorage.setItem('{{ $announcementDismissKey }}', '1')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 opacity-70 hover:opacity-100 text-lg leading-none"
                    aria-label="Dismiss">&times;</button>
        </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Policies -->
    @php
        $showPolicies = $settings->showPolicies;
        $policies = $showPolicies ? array_filter([
            'Cancellation Policy' => $settings->cancellationPolicy,
            'Deposit Policy' => $settings->depositPolicy,
            'Refund Policy' => $settings->refundPolicy,
            'Pickup Policy' => $settings->pickupPolicy,
            'Additional Terms' => $settings->additionalTerms,
        ]) : [];
    @endphp

    @if (!empty($policies))
    <section style="background: var(--warm-50); border-top: 1px solid var(--warm-200);" class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <h3 class="font-display text-2xl text-center mb-8 text-warm-700">Policies & Terms</h3>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($policies as $label => $text)
                <div class="card p-5">
                    <h4 class="font-display text-lg mb-2 text-warm-600">{{ $label }}</h4>
                    <p class="text-sm leading-relaxed text-warm-700">{{ $text }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer style="background: var(--warm-900); color: var(--warm-200);" class="py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="h-1 mb-12" style="background: linear-gradient(to right, transparent, var(--warm-500), transparent);"></div>

            <h3 class="font-display text-2xl mb-2">{{ $settings->storeName }}</h3>
            <p class="font-script text-xl mb-6 text-warm-400">Baked with love, served with care</p>

            @php
                $footerAddress = $settings->storeAddress;
                $footerPhone = $settings->storePhone;
                $footerEmail = $settings->storeEmail;
            @endphp

            @php
                $footerSocial = $settings->socialMediaLinks;
            @endphp

            @if (!empty(array_filter($footerSocial ?? [])))
            <div class="flex justify-center gap-4 mb-6">
                @if (!empty($footerSocial['facebook']))
                <a href="{{ $footerSocial['facebook'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                @endif
                @if (!empty($footerSocial['instagram']))
                <a href="{{ $footerSocial['instagram'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                @endif
                @if (!empty($footerSocial['twitter']))
                <a href="{{ $footerSocial['twitter'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                </a>
                @endif
            </div>
            @endif

            @if ($footerAddress || $footerPhone || $footerEmail)
            <div class="flex flex-wrap justify-center gap-6 mb-8 text-sm text-warm-400">
                @if ($footerAddress)
                <span>{{ $footerAddress }}</span>
                @endif
                @if ($footerPhone)
                <span>{{ $footerPhone }}</span>
                @endif
                @if ($footerEmail)
                <span>{{ $footerEmail }}</span>
                @endif
            </div>
            @endif

            <div class="text-sm leading-relaxed space-y-3 text-warm-400">
                <p>&copy; {{ date('Y') }} {{ $settings->storeName }}. All rights reserved.</p>
                <p class="text-xs opacity-60">Powered by KneadIt</p>
            </div>
        </div>
    </footer>

    {{-- PWA Install Prompt --}}
    <div id="pwaInstall" style="display:none;position:fixed;bottom:5rem;right:1.5rem;background:#1c1410;color:#faf4e8;padding:1rem 1.25rem;border-radius:16px;z-index:9998;box-shadow:0 8px 32px rgba(0,0,0,.4);font-size:.85rem;max-width:280px;border:1px solid rgba(212,146,12,.15)">
        <div style="display:flex;align-items:start;gap:.75rem">
            <div style="flex:1">
                <strong style="color:#d4920c;font-size:.9rem">Add to Home Screen</strong>
                <p style="margin:.25rem 0 .75rem;color:#8b6844;line-height:1.4;font-size:.8rem">Quick access to your favorite bakery — no app store needed.</p>
                <div style="display:flex;gap:.5rem">
                    <button id="pwaInstallBtn" style="padding:.4rem 1rem;border-radius:50px;background:#d4920c;color:#fff;border:none;font-weight:700;font-size:.75rem;cursor:pointer">Install</button>
                    <button onclick="dismissPwa()" style="padding:.4rem .75rem;border-radius:50px;background:transparent;color:#8b6844;border:1px solid #4a3728;font-size:.75rem;cursor:pointer">Not now</button>
                </div>
            </div>
            <button onclick="dismissPwa()" style="background:none;border:none;color:#8b6844;cursor:pointer;font-size:1.1rem;padding:0;line-height:1">&times;</button>
        </div>
    </div>
    <script>
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt',function(e){e.preventDefault();deferredPrompt=e;if(!localStorage.getItem('pwaDismissed')){document.getElementById('pwaInstall').style.display='block'}});
    document.getElementById('pwaInstallBtn').addEventListener('click',function(){if(deferredPrompt){deferredPrompt.prompt();deferredPrompt.userChoice.then(function(){deferredPrompt=null;document.getElementById('pwaInstall').style.display='none'})}});
    function dismissPwa(){document.getElementById('pwaInstall').style.display='none';localStorage.setItem('pwaDismissed','1')}
    </script>

    {{-- Cookie Consent Banner --}}
    <div id="cookieConsent" style="display:none;position:fixed;bottom:0;left:0;right:0;background:#1c1410;color:#faf4e8;padding:1rem 1.5rem;z-index:9999;box-shadow:0 -4px 20px rgba(0,0,0,.3);font-size:.85rem;line-height:1.5">
        <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <p style="margin:0;flex:1;min-width:200px">We use cookies to improve your experience. By continuing to browse, you agree to our use of cookies.
                <a href="/privacy" style="color:#d4920c;text-decoration:underline">Privacy Policy</a>
            </p>
            <button onclick="acceptCookies()" style="padding:.5rem 1.5rem;border-radius:50px;background:#d4920c;color:#fff;border:none;font-weight:700;font-size:.8rem;cursor:pointer;white-space:nowrap;transition:background .2s">Accept</button>
        </div>
    </div>
    <script>
    function acceptCookies(){document.getElementById('cookieConsent').style.display='none';localStorage.setItem('cookieConsent','1')}
    if(!localStorage.getItem('cookieConsent')){document.getElementById('cookieConsent').style.display='block'}
    </script>

    {{ $scripts ?? "" }}

</body>
</html>