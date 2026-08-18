@php
    $aboutUs = $settings->branding->aboutUsText;
    $storeName = $settings->store->name;
    $storePhoto = $settings->store->photo;
@endphp
@if ($aboutUs)
    <section class="bg-warm-50 relative overflow-hidden px-4 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="grid items-center gap-12 md:grid-cols-5 md:gap-16">
                {{-- Left: Photo or decorative element --}}
                <div class="relative md:col-span-2">
                    @if ($storePhoto)
                        <div class="relative">
                            <div class="overflow-hidden rounded-2xl shadow-xl">
                                <img
                                    src="{{ Storage::url($storePhoto) }}"
                                    alt="{{ $storeName }}"
                                    class="h-auto w-full object-cover"
                                    style="aspect-ratio: 3/4"
                                />
                            </div>
                            {{-- Decorative frame offset --}}
                            <div
                                class="absolute -right-4 -bottom-4 -z-10 h-full w-full rounded-2xl"
                                style="border: 1px solid var(--warm-300)"
                            ></div>
                        </div>
                    @else
                        {{-- Decorative typography block when no photo --}}
                        <div class="bg-warm-200 relative rounded-2xl p-12 text-center">
                            <div
                                class="font-display leading-none font-bold"
                                style="font-size: 8rem; color: var(--warm-300); opacity: 0.5"
                            >
                                {{ strtoupper(substr($storeName, 0, 1)) }}
                            </div>
                            <p class="font-script text-warm-500 mt-4 text-xl">Est. {{ date('Y') }}</p>
                            {{-- Corner accents --}}
                            <div
                                class="absolute top-4 left-4 h-8 w-8"
                                style="
                                    border-top: 2px solid var(--warm-400);
                                    border-left: 2px solid var(--warm-400);
                                    opacity: 0.3;
                                "
                            ></div>
                            <div
                                class="absolute right-4 bottom-4 h-8 w-8"
                                style="
                                    border-bottom: 2px solid var(--warm-400);
                                    border-right: 2px solid var(--warm-400);
                                    opacity: 0.3;
                                "
                            ></div>
                        </div>
                    @endif
                </div>

                {{-- Right: Content --}}
                <div class="md:col-span-3">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="bg-warm-500 block h-px w-8"></span>
                        <span class="text-warm-500 text-xs font-semibold tracking-[0.25em] uppercase">Our Story</span>
                    </div>

                    <h2 class="font-display text-warm-900 mb-6 text-3xl leading-tight font-bold md:text-5xl">
                        A little about<br />{{ $storeName }}
                    </h2>

                    <p class="text-warm-600 mb-8 text-lg leading-relaxed">{{ $aboutUs }}</p>

                    <div class="flex items-center gap-4">
                        <a
                            href="{{ route('storefront.about') }}"
                            class="text-warm-700 inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3"
                        >
                            Read Our Full Story
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
