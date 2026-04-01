@php
    $aboutUs = $settings->aboutUsText;
    $storeName = $settings->storeName;
    $storePhoto = $settings->storePhoto;
@endphp
@if ($aboutUs)
<section class="relative py-24 px-4 overflow-hidden bg-warm-50">
    <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-5 gap-12 md:gap-16 items-center">
            {{-- Left: Photo or decorative element --}}
            <div class="md:col-span-2 relative">
                @if ($storePhoto)
                <div class="relative">
                    <div class="rounded-2xl overflow-hidden shadow-xl">
                        <img src="{{ Storage::url($storePhoto) }}" alt="{{ $storeName }}" class="w-full h-auto object-cover" style="aspect-ratio: 3/4;">
                    </div>
                    {{-- Decorative frame offset --}}
                    <div class="absolute -bottom-4 -right-4 w-full h-full rounded-2xl -z-10" style="border: 1px solid var(--warm-300);"></div>
                </div>
                @else
                {{-- Decorative typography block when no photo --}}
                <div class="relative rounded-2xl p-12 text-center bg-warm-200">
                    <div class="font-display font-bold leading-none" style="font-size: 8rem; color: var(--warm-300); opacity: 0.5;">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                    <p class="font-script text-xl mt-4 text-warm-500">Est. {{ date('Y') }}</p>
                    {{-- Corner accents --}}
                    <div class="absolute top-4 left-4 w-8 h-8" style="border-top: 2px solid var(--warm-400); border-left: 2px solid var(--warm-400); opacity: 0.3;"></div>
                    <div class="absolute bottom-4 right-4 w-8 h-8" style="border-bottom: 2px solid var(--warm-400); border-right: 2px solid var(--warm-400); opacity: 0.3;"></div>
                </div>
                @endif
            </div>

            {{-- Right: Content --}}
            <div class="md:col-span-3">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-8 h-px bg-warm-500"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">Our Story</span>
                </div>

                <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 leading-tight text-warm-900">
                    A little about<br>{{ $storeName }}
                </h2>

                <p class="text-lg leading-relaxed mb-8 text-warm-600">
                    {{ $aboutUs }}
                </p>

                <div class="flex items-center gap-4">
                    <a href="{{ route('storefront.about') }}" class="inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3 text-warm-700">
                        Read Our Full Story
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
