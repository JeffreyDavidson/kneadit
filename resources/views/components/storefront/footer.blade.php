@props(['settings'])

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
