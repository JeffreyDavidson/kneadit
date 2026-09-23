@props(['storefrontTheme', 'storeName', 'cateringEnabled', 'loyaltyEnabled', 'loyaltyName', 'exploreActive', 'accountActive'])

<!-- Navigation -->
@if ($storefrontTheme === 'biscotto')
    <nav class="biscotto-nav" x-data="{ open: false }">
        <a href="{{ url('/') }}" class="biscotto-nav-brand">{{ $storeName }}</a>
        <div class="biscotto-nav-links">
            <a href="{{ url('/') }}" @class(['active' => request()->routeIs('storefront.home')])>Home</a>
            <a href="{{ route('storefront.about') }}" @class(['active' => request()->routeIs('storefront.about')])
                >About</a>
            <a href="{{ route('storefront.menu') }}" @class(['active' => request()->routeIs('storefront.menu')])
                >Menu</a>
            <a href="{{ route('storefront.gallery') }}" @class(['active' => request()->routeIs('storefront.gallery')])
                >Gallery</a>
            <a href="{{ route('contact.show') }}#faq">FAQ</a>
            <a href="{{ route('order.create') }}" @class(['active' => request()->routeIs('order.create')])>Order</a>
            <a href="{{ route('contact.show') }}" @class(['active' => request()->routeIs('contact.show')])>Contact</a>
        </div>
        <button @click="open = ! open" class="biscotto-nav-toggle" aria-label="Toggle navigation" :aria-expanded="open">
            <x-heroicon-o-bars-3 x-show="! open" class="h-6 w-6" />
            <x-heroicon-o-x-mark x-show="open" x-cloak class="h-6 w-6" />
        </button>
        <div x-show="open" x-collapse class="biscotto-mobile-links">
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ route('storefront.about') }}">About</a>
            <a href="{{ route('storefront.menu') }}">Menu</a>
            <a href="{{ route('storefront.gallery') }}">Gallery</a>
            <a href="{{ route('contact.show') }}#faq">FAQ</a>
            <a href="{{ route('order.create') }}">Order</a>
            <a href="{{ route('contact.show') }}">Contact</a>
        </div>
    </nav>
@else
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
                    @class(['nav-link font-display', 'active' => request()->routeIs('storefront.menu')])
                >Menu</a>
                <a
                    href="{{ route('order.create') }}"
                    @class(['nav-link font-display', 'active' => request()->routeIs('order.create')])
                >Order</a>

                <!-- Explore Dropdown -->
                <div class="relative">
                    <button
                        @click="
                            explore = ! explore;
                            account = false;
                        "
                        @class(['nav-link font-display inline-flex items-center gap-1', 'active' => $exploreActive])
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
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.blog*')])
                        >Blog</a>
                        <a
                            href="{{ route('storefront.gallery') }}"
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.gallery')])
                        >Gallery</a>
                        <a
                            href="{{ route('storefront.reviews') }}"
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.reviews')])
                        >Reviews</a>
                        <a
                            href="{{ route('storefront.about') }}"
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.about')])
                        >About</a>
                        @if ($cateringEnabled)
                            <a
                                href="{{ route('storefront.catering') }}"
                                @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.catering')])
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
                        @class(['nav-link font-display inline-flex items-center gap-1', 'active' => $accountActive])
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
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('order.track')])
                        >Track Order</a>
                        <a
                            href="{{ route('storefront.giftCards') }}"
                            @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.giftCards')])
                        >Gift Cards</a>
                        @if ($loyaltyEnabled)
                            <a
                                href="{{ route('storefront.rewards') }}"
                                @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('storefront.rewards')])
                            >{{ $loyaltyName }}</a>
                        @endif
                        @auth('customer')
                            <a
                                href="{{ route('account.dashboard') }}"
                                @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('account.dashboard')])
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
                                @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('account.login.show')])
                            >Sign in</a>
                            <a
                                href="{{ route('account.register.show') }}"
                                @class(['nav-dropdown-link font-display', 'active' => request()->routeIs('account.register.show')])
                            >Create account</a>
                        @endauth
                    </div>
                </div>

                <a
                    href="{{ route('contact.show') }}"
                    @class(['nav-link font-display', 'active' => request()->routeIs('contact.show')])
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
                        @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.menu')])
                    >Menu</a>
                    <a
                        href="{{ route('order.create') }}"
                        @class(['block nav-link font-display', 'active' => request()->routeIs('order.create')])
                    >Order</a>

                    <!-- Mobile Explore Group -->
                    <button
                        @click="explore = ! explore"
                        @class(['w-full text-left nav-link font-display inline-flex items-center justify-between', 'active' => $exploreActive])
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
                            @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.blog*')])
                        >Blog</a>
                        <a
                            href="{{ route('storefront.gallery') }}"
                            @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.gallery')])
                        >Gallery</a>
                        <a
                            href="{{ route('storefront.reviews') }}"
                            @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.reviews')])
                        >Reviews</a>
                        <a
                            href="{{ route('storefront.about') }}"
                            @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.about')])
                        >About</a>
                        @if ($cateringEnabled)
                            <a
                                href="{{ route('storefront.catering') }}"
                                @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.catering')])
                            >Catering</a>
                        @endif
                    </div>

                    <!-- Mobile Account Group -->
                    <button
                        @click="account = ! account"
                        @class(['w-full text-left nav-link font-display inline-flex items-center justify-between', 'active' => $accountActive])
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
                            @class(['block nav-link font-display', 'active' => request()->routeIs('order.track')])
                        >Track Order</a>
                        <a
                            href="{{ route('storefront.giftCards') }}"
                            @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.giftCards')])
                        >Gift Cards</a>
                        @if ($loyaltyEnabled)
                            <a
                                href="{{ route('storefront.rewards') }}"
                                @class(['block nav-link font-display', 'active' => request()->routeIs('storefront.rewards')])
                            >{{ $loyaltyName }}</a>
                        @endif
                        @auth('customer')
                            <a
                                href="{{ route('account.dashboard') }}"
                                @class(['block nav-link font-display', 'active' => request()->routeIs('account.dashboard')])
                            >My dashboard</a>
                            <form method="POST" action="{{ route('account.logout') }}">
                                @csrf
                                <button type="submit" class="nav-link font-display block w-full text-left">
                                    Sign out
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('account.login.show') }}"
                                @class(['block nav-link font-display', 'active' => request()->routeIs('account.login.show')])
                            >Sign in</a>
                            <a
                                href="{{ route('account.register.show') }}"
                                @class(['block nav-link font-display', 'active' => request()->routeIs('account.register.show')])
                            >Create account</a>
                        @endauth
                    </div>

                    <a
                        href="{{ route('contact.show') }}"
                        @class(['block nav-link font-display', 'active' => request()->routeIs('contact.show')])
                    >Contact</a>
                </div>
            </div>
        </div>
    </nav>
@endif
