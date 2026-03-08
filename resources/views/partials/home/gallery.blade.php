@php
    $count = $config['count'] ?? 4;
    $title = $config['title'] ?? 'Customer Gallery';
    $subtitle = $config['subtitle'] ?? 'Shared by our community';
    try {
        $customerPhotos = \App\Models\CustomerPhoto::approved()->featured()->with('product')->latest()->take($count)->get();
    } catch (\Exception $e) {
        $customerPhotos = collect();
    }
@endphp
@if($customerPhotos->count() > 0)
<style>
    .masonry-gallery {
        columns: 2;
        column-gap: 1rem;
    }
    @media (min-width: 768px) {
        .masonry-gallery { columns: 3; column-gap: 1.5rem; }
    }
    @media (min-width: 1024px) {
        .masonry-gallery { columns: 4; column-gap: 1.5rem; }
    }
    .masonry-gallery > * {
        break-inside: avoid;
        margin-bottom: 1.5rem;
    }
</style>
<section class="py-20 px-4" style="background: var(--warm-200);">
    <div class="max-w-6xl mx-auto">
        <h2 class="font-display text-3xl md:text-5xl font-semibold text-center mb-4" style="color: var(--warm-900);">{{ $title }}</h2>
        <p class="text-center mb-14 text-base" style="color: var(--warm-600);">{{ $subtitle }}</p>

        <div class="masonry-gallery">
            @foreach($customerPhotos as $i => $photo)
            <div class="rounded-xl overflow-hidden group" style="background: white;">
                <div class="overflow-hidden">
                    <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                         alt="Photo by {{ $photo->customer_name }}"
                         class="w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy"
                         style="height: {{ $i % 3 === 0 ? '280px' : ($i % 3 === 1 ? '200px' : '240px') }};">
                </div>
                <div class="p-4">
                    @if($photo->caption)
                    <p class="text-sm italic mb-1" style="color: var(--warm-700);">"{{ Str::limit($photo->caption, 60) }}"</p>
                    @endif
                    <p class="text-xs font-semibold" style="color: var(--warm-500);">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.gallery') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-700);">
                View Full Gallery
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
