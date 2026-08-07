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

    @include('components.layouts.storefront-styles')
    {{-- Original style block replaced by include above --}}

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
        @include('partials.fathom')
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

    <!-- Navigation -->
    <nav class="fixed top-4 left-1/2 z-50 w-max max-w-[95vw] -translate-x-1/2 transform">
        <div class="bg-warm-800 border-warm-700/20 rounded-full border px-6 py-3 backdrop-blur-sm">
            <!-- Desktop Nav -->
            <div
                class="nav-desktop items-center space-x-1"
                x-data="{ explore: false, account: false }"
                @click.outside="
                    explore = false;
                    account = false;
                "
            >
                <a href="{{ url('/') }}" class="nav-link font-script text-warm-400 px-4 py-2 text-[1.15rem]">
                    {{ $storeName }}
                </a>
                <span class="text-warm-600 opacity-30">|</span>

                <a
                    href="{{ route('storefront.menu') }}"
                    class="nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}"
                >Menu</a>
                <a
                    href="{{ route('order.create') }}"
                    class="nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}"
                >Order</a>

                <!-- Explore Dropdown -->
                <div class="relative">
                    <button
                        @click="
                            explore = ! explore;
                            account = false;
                        "
                        class="nav-link font-display inline-flex items-center gap-1 {{ $exploreActive ? 'active' : '' }}"
                        :aria-expanded="explore"
                    >
                        Explore
                        <x-heroicon-o-chevron-down
                            class="h-3.5 w-3.5 transition-transform"
                            ::class="explore ? 'rotate-180' : ''"
                            stroke-width="2.5"
                        />
                    </button>
                    <div
                        x-show="explore"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="bg-warm-800 border-warm-700/25 absolute top-full left-1/2 mt-3 min-w-[180px] -translate-x-1/2 rounded-xl border py-2 shadow-xl"
                    >
                        <a
                            href="{{ route('storefront.blog') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.blog*') ? 'active' : '' }}"
                        >Blog</a>
                        <a
                            href="{{ route('storefront.gallery') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.gallery') ? 'active' : '' }}"
                        >Gallery</a>
                        <a
                            href="{{ route('storefront.reviews') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.reviews') ? 'active' : '' }}"
                        >Reviews</a>
                        <a
                            href="{{ route('storefront.about') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.about') ? 'active' : '' }}"
                        >About</a>
                        @if ($cateringEnabled)
                        <a
                            href="{{ route('storefront.catering') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.catering') ? 'active' : '' }}"
                        >Catering</a>
                        @endif
                    </div>
                </div>

                <!-- My Account Dropdown -->
                <div class="relative">
                    <button
                        @click="
                            account = ! account;
                            explore = false;
                        "
                        class="nav-link font-display inline-flex items-center gap-1 {{ $accountActive ? 'active' : '' }}"
                        :aria-expanded="account"
                    >
                        My Account
                        <x-heroicon-o-chevron-down
                            class="h-3.5 w-3.5 transition-transform"
                            ::class="account ? 'rotate-180' : ''"
                            stroke-width="2.5"
                        />
                    </button>
                    <div
                        x-show="account"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="bg-warm-800 border-warm-700/25 absolute top-full left-1/2 mt-3 min-w-[180px] -translate-x-1/2 rounded-xl border py-2 shadow-xl"
                    >
                        <a
                            href="{{ route('order.track') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('order.track') ? 'active' : '' }}"
                        >Track Order</a>
                        <a
                            href="{{ route('storefront.giftCards') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.giftCards') ? 'active' : '' }}"
                        >Gift Cards</a>
                        @if ($loyaltyEnabled)
                        <a
                            href="{{ route('storefront.rewards') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('storefront.rewards') ? 'active' : '' }}"
                        >{{ $loyaltyName }}</a>
                        @endif
                        @auth('customer')
                        <a
                            href="{{ route('account.dashboard') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('account.dashboard') ? 'active' : '' }}"
                        >My dashboard</a>
                        <form method="POST" action="{{ route('account.logout') }}">
                            @csrf
                            <button type="submit" class="nav-dropdown-link font-display w-full text-left">
                                Sign out
                            </button>
                        </form>
                        @else
                        <a
                            href="{{ route('account.login.show') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('account.login.show') ? 'active' : '' }}"
                        >Sign in</a>
                        <a
                            href="{{ route('account.register.show') }}"
                            class="nav-dropdown-link font-display {{ request()->routeIs('account.register.show') ? 'active' : '' }}"
                        >Create account</a>
                        @endauth
                    </div>
                </div>

                <a
                    href="{{ route('contact.show') }}"
                    class="nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}"
                >Contact</a>
            </div>

            <!-- Mobile Nav -->
            <div class="nav-mobile" x-data="{ open: false, explore: false, account: false }">
                <div class="flex items-center justify-between">
                    <a href="{{ url('/') }}" class="font-script text-warm-400 text-lg no-underline">
                        {{ $storeName }}
                    </a>
                    <button
                        @click="open = ! open"
                        class="nav-link font-display ml-4 px-3 py-2"
                        aria-label="Toggle navigation"
                        :aria-expanded="open"
                    >
                        <x-heroicon-o-bars-3 x-show="! open" class="h-5 w-5" stroke-width="2" />
                        <x-heroicon-o-x-mark x-show="open" x-cloak class="h-5 w-5" stroke-width="2" />
                    </button>
                </div>
                <div x-show="open" x-collapse class="mt-4 space-y-1">
                    <a
                        href="{{ route('storefront.menu') }}"
                        class="block nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}"
                    >Menu</a>
                    <a
                        href="{{ route('order.create') }}"
                        class="block nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}"
                    >Order</a>

                    <!-- Mobile Explore Group -->
                    <button
                        @click="explore = ! explore"
                        class="w-full text-left nav-link font-display inline-flex items-center justify-between {{ $exploreActive ? 'active' : '' }}"
                        :aria-expanded="explore"
                    >
                        Explore
                        <x-heroicon-o-chevron-down
                            class="h-3.5 w-3.5 transition-transform"
                            ::class="explore ? 'rotate-180' : ''"
                            stroke-width="2.5"
                        />
                    </button>
                    <div x-show="explore" x-collapse class="space-y-1 pl-4">
                        <a
                            href="{{ route('storefront.blog') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.blog*') ? 'active' : '' }}"
                        >Blog</a>
                        <a
                            href="{{ route('storefront.gallery') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.gallery') ? 'active' : '' }}"
                        >Gallery</a>
                        <a
                            href="{{ route('storefront.reviews') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.reviews') ? 'active' : '' }}"
                        >Reviews</a>
                        <a
                            href="{{ route('storefront.about') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.about') ? 'active' : '' }}"
                        >About</a>
                        @if ($cateringEnabled)
                        <a
                            href="{{ route('storefront.catering') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.catering') ? 'active' : '' }}"
                        >Catering</a>
                        @endif
                    </div>

                    <!-- Mobile Account Group -->
                    <button
                        @click="account = ! account"
                        class="w-full text-left nav-link font-display inline-flex items-center justify-between {{ $accountActive ? 'active' : '' }}"
                        :aria-expanded="account"
                    >
                        My Account
                        <x-heroicon-o-chevron-down
                            class="h-3.5 w-3.5 transition-transform"
                            ::class="account ? 'rotate-180' : ''"
                            stroke-width="2.5"
                        />
                    </button>
                    <div x-show="account" x-collapse class="space-y-1 pl-4">
                        <a
                            href="{{ route('order.track') }}"
                            class="block nav-link font-display {{ request()->routeIs('order.track') ? 'active' : '' }}"
                        >Track Order</a>
                        <a
                            href="{{ route('storefront.giftCards') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.giftCards') ? 'active' : '' }}"
                        >Gift Cards</a>
                        @if ($loyaltyEnabled)
                        <a
                            href="{{ route('storefront.rewards') }}"
                            class="block nav-link font-display {{ request()->routeIs('storefront.rewards') ? 'active' : '' }}"
                        >{{ $loyaltyName }}</a>
                        @endif
                        @auth('customer')
                        <a
                            href="{{ route('account.dashboard') }}"
                            class="block nav-link font-display {{ request()->routeIs('account.dashboard') ? 'active' : '' }}"
                        >My dashboard</a>
                        <form method="POST" action="{{ route('account.logout') }}">
                            @csrf
                            <button type="submit" class="nav-link font-display block w-full text-left">Sign out</button>
                        </form>
                        @else
                        <a
                            href="{{ route('account.login.show') }}"
                            class="block nav-link font-display {{ request()->routeIs('account.login.show') ? 'active' : '' }}"
                        >Sign in</a>
                        <a
                            href="{{ route('account.register.show') }}"
                            class="block nav-link font-display {{ request()->routeIs('account.register.show') ? 'active' : '' }}"
                        >Create account</a>
                        @endauth
                    </div>

                    <a
                        href="{{ route('contact.show') }}"
                        class="block nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}"
                    >Contact</a>
                </div>
            </div>
        </div>
    </nav>

    @php
        $pageTestId = 'page-' . str_replace(['storefront.', '.'], ['', '-'], request()->route()?->getName() ?? 'unknown');
    @endphp
    <main class="min-h-screen pt-24" data-test="{{ $pageTestId }}">
        @php
            $announcementEnabled = $settings->engagement->announcementEnabled ? '1' : '0';
            $announcementText = $settings->engagement->announcementText;
            $announcementType = $settings->engagement->announcementType;
            $announcementDismissKey = $announcementText
                ? 'announcement_dismissed_' . hash('xxh128', $announcementText)
                : null;
        @endphp

        @if ($announcementEnabled === '1' && $announcementDismissKey)
        @php
            $announcementClass = match ($announcementType) {
                'warning' => 'bg-yellow-100 text-yellow-800 border-b-2 border-yellow-500',
                'success' => 'bg-green-100 text-green-800 border-b-2 border-green-600',
                'holiday' => 'bg-gradient-to-br from-red-700 to-green-800 text-white border-b-2 border-yellow-400',
                default => 'bg-warm-200 text-warm-900 border-b-2 border-warm-500',
            };
        @endphp
        <div
            x-data="{ show: ! localStorage.getItem('{{ $announcementDismissKey }}') }"
            x-show="show"
            x-transition
            class="relative px-4 py-3 text-center text-sm font-medium {{ $announcementClass }}"
        >
            <span>{{ $announcementText }}</span>
            <button
                @click="show = false; localStorage.setItem('{{ $announcementDismissKey }}', '1')"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-lg leading-none opacity-70 hover:opacity-100"
                aria-label="Dismiss"
            >
                &times;
            </button>
        </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Policies -->
    @php
        $showPolicies = $settings->policies->showOnStorefront;
        $policies = $showPolicies ? array_filter([
            'Cancellation Policy' => $settings->policies->cancellation,
            'Deposit Policy' => $settings->policies->deposit,
            'Refund Policy' => $settings->policies->refund,
            'Pickup Policy' => $settings->policies->pickup,
            'Additional Terms' => $settings->policies->additionalTerms,
        ]) : [];
    @endphp

    @if (! empty($policies))
    <section class="bg-warm-50 border-warm-200 border-t py-12">
        <div class="mx-auto max-w-4xl px-4">
            <h3 class="font-display text-warm-700 mb-8 text-center text-2xl">Policies & Terms</h3>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($policies as $label => $text)
                <div class="card p-5">
                    <h4 class="font-display text-warm-600 mb-2 text-lg">{{ $label }}</h4>
                    <p class="text-warm-700 text-sm leading-relaxed">{{ $text }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer class="bg-warm-900 text-warm-200 py-16">
        <div class="mx-auto max-w-6xl px-4 text-center">
            <div class="via-warm-500 mb-12 h-1 bg-gradient-to-r from-transparent to-transparent"></div>

            <h3 class="font-display mb-2 text-2xl">{{ $settings->store->name }}</h3>
            <p class="font-script text-warm-400 mb-6 text-xl">{{ $settings->defaultTagline() }}</p>

            @php
                $footerAddress = $settings->store->address;
                $footerPhone = $settings->store->phone;
                $footerEmail = $settings->store->email;
            @endphp

            @php
                $footerSocial = $settings->homepage->socialMediaLinks;
            @endphp

            <x-storefront.social-links
                :links="$footerSocial"
                size="w-10 h-10"
                class="mb-6 justify-center"
                link-class="[background:rgba(255,255,255,0.1)] text-warm-400"
            />

            @if ($footerAddress || $footerPhone || $footerEmail)
            <div class="text-warm-400 mb-8 flex flex-wrap justify-center gap-6 text-sm">
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

            <div class="text-warm-400 space-y-3 text-sm leading-relaxed">
                <p>&copy; {{ date('Y') }} {{ $settings->store->name }}. All rights reserved.</p>
                <p class="text-xs opacity-60">Powered by KneadIt</p>
            </div>
        </div>
    </footer>

    {{-- PWA Install Prompt --}}
    <div
        id="pwaInstall"
        class="bg-warm-900 text-warm-200 border-warm-500/15 fixed right-6 bottom-20 z-[9998] hidden max-w-[280px] rounded-2xl border px-5 py-4 text-[0.85rem] shadow-2xl"
    >
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <strong class="text-warm-500 text-[0.9rem]">Add to Home Screen</strong>
                <p class="text-warm-600 mt-1 mb-3 text-[0.8rem] leading-snug">
                    Quick access to your favorite bakery — no app store needed.
                </p>
                <div class="flex gap-2">
                    <button
                        id="pwaInstallBtn"
                        class="bg-warm-500 cursor-pointer rounded-full border-0 px-4 py-1.5 text-xs font-bold text-white"
                    >
                        Install
                    </button>
                    <button
                        onclick="dismissPwa()"
                        class="text-warm-600 border-warm-700 cursor-pointer rounded-full border bg-transparent px-3 py-1.5 text-xs"
                    >
                        Not now
                    </button>
                </div>
            </div>
            <button
                onclick="dismissPwa()"
                class="text-warm-600 cursor-pointer border-0 bg-transparent p-0 text-[1.1rem] leading-none"
                aria-label="Dismiss install prompt"
            >
                &times;
            </button>
        </div>
    </div>
    <script @cspnonce>
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            if (!localStorage.getItem('pwaDismissed')) {
                document.getElementById('pwaInstall').style.display = 'block';
            }
        });
        document.getElementById('pwaInstallBtn').addEventListener('click', function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function () {
                    deferredPrompt = null;
                    document.getElementById('pwaInstall').style.display = 'none';
                });
            }
        });
        function dismissPwa() {
            document.getElementById('pwaInstall').style.display = 'none';
            localStorage.setItem('pwaDismissed', '1');
        }
    </script>

    {{-- Cookie Consent Banner --}}
    <div
        id="cookieConsent"
        role="region"
        aria-label="Cookie consent"
        class="bg-warm-900 text-warm-200 fixed right-0 bottom-0 left-0 z-[9999] hidden px-6 py-4 text-[0.85rem] leading-relaxed shadow-2xl"
    >
        <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-4">
            <p class="m-0 min-w-[200px] flex-1">
                We use cookies to improve your experience. By continuing to browse, you agree to our use of cookies.
                <a href="/privacy" class="text-warm-500 underline">Privacy Policy</a>
            </p>
            <button
                onclick="acceptCookies()"
                class="bg-warm-500 cursor-pointer rounded-full border-0 px-6 py-2 text-xs font-bold whitespace-nowrap text-white transition-colors"
            >
                Accept
            </button>
        </div>
    </div>
    <script @cspnonce>
        function acceptCookies() {
            document.getElementById('cookieConsent').style.display = 'none';
            localStorage.setItem('cookieConsent', '1');
        }
        if (!localStorage.getItem('cookieConsent')) {
            document.getElementById('cookieConsent').style.display = 'block';
        }
    </script>

    {{ $scripts ?? "" }}
</body>
</html>
