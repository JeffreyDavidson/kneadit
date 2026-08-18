@if ($customerPhotos->count() > 0)
<style @cspnonce>
    .gallery-masonry {
        columns: 2;
        column-gap: 12px;
    }
    @media (min-width: 768px) {
        .gallery-masonry { columns: 3; column-gap: 16px; }
    }
    .gallery-masonry > * {
        break-inside: avoid;
        margin-bottom: 16px;
    }
</style>

<section class="py-24 px-4 bg-warm-900">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-14">
            <x-storefront.eyebrow line-opacity="0.5" class="mb-4">Gallery</x-storefront.eyebrow>
            <h2 class="font-display text-3xl md:text-5xl font-bold mb-3 text-warm-100">{{ $title }}</h2>
            <p class="text-base text-warm-500">{{ $subtitle }}</p>
        </div>

        <div class="gallery-masonry">
            @foreach ($customerPhotos as $i => $photo)
            <div class="group rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl bg-warm-800">
                <div class="relative overflow-hidden">
                    <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                         alt="Photo by {{ $photo->customer_name }}"
                         class="w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy"
                         style="height: {{ [280, 220, 320, 200, 260, 240][$i % 6] }}px;">
                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end" style="background: linear-gradient(to top, rgba(28,20,16,0.8), transparent 60%);">
                        <div class="p-4">
                            @if ($photo->caption)
                            <p class="italic text-sm mb-1 text-warm-200">"{{ Str::limit($photo->caption, 60) }}"</p>
                            @endif
                            <p class="text-xs font-semibold text-warm-500">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('storefront.gallery') }}" class="inline-flex items-center gap-2 font-semibold transition-all duration-200 hover:gap-3 text-warm-400">
                View Full Gallery
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
