@if ($customerPhotos->count() > 0)
    <style @cspnonce>
        .gallery-masonry {
            columns: 2;
            column-gap: 12px;
        }
        @media (min-width: 768px) {
            .gallery-masonry {
                columns: 3;
                column-gap: 16px;
            }
        }
        .gallery-masonry > * {
            break-inside: avoid;
            margin-bottom: 16px;
        }
    </style>

    <section class="bg-warm-900 px-4 py-24">
        <div class="mx-auto max-w-6xl">
            {{-- Header --}}
            <div class="mb-14 text-center">
                <x-storefront.eyebrow line-opacity="0.5" class="mb-4">Gallery</x-storefront.eyebrow>
                <h2 class="font-display text-warm-100 mb-3 text-3xl font-bold md:text-5xl">{{ $title }}</h2>
                <p class="text-warm-500 text-base">{{ $subtitle }}</p>
            </div>

            <div class="gallery-masonry">
                @foreach ($customerPhotos as $i => $photo)
                    <div class="group bg-warm-800 overflow-hidden rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <div class="relative overflow-hidden">
                            <img
                                src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                                alt="Photo by {{ $photo->customer_name }}"
                                class="w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                                style="height: {{ [280, 220, 320, 200, 260, 240][$i % 6] }}px;"
                            />
                            {{-- Hover overlay --}}
                            <div
                                class="absolute inset-0 flex items-end opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                style="background: linear-gradient(to top, rgba(28, 20, 16, 0.8), transparent 60%)"
                            >
                                <div class="p-4">
                                    @if ($photo->caption)
                                        <p class="text-warm-200 mb-1 text-sm italic">
                                            "{{ Str::limit($photo->caption, 60) }}"
                                        </p>
                                    @endif
                                    <p class="text-warm-500 text-xs font-semibold">
                                        {{ Str::of($photo->customer_name)->explode(' ')->first() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <a
                    href="{{ route('storefront.gallery') }}"
                    class="text-warm-400 inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3"
                >
                    View Full Gallery
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>
@endif
