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

<x-storefront.divider style="ornament" width="md" />

<x-storefront.section bg="cream" padding="xl" maxWidth="6xl">
    <x-storefront.section-header
        eyebrow="Community"
        :title="$title"
        :subtitle="$subtitle"
        align="center"
    />

    <div class="masonry-gallery">
        @foreach($customerPhotos as $i => $photo)
        <div class="group rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: white;">
            <div class="overflow-hidden">
                <img src="{{ asset('storage/customer-photos/' . basename($photo->photo_path)) }}"
                     alt="Photo by {{ $photo->customer_name }}"
                     class="w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy"
                     style="height: {{ $i % 3 === 0 ? '280px' : ($i % 3 === 1 ? '200px' : '240px') }};">
            </div>
            <div class="p-4">
                @if($photo->caption)
                <p class="font-body text-sm italic mb-1" style="color: var(--warm-700);">&ldquo;{{ Str::limit($photo->caption, 60) }}&rdquo;</p>
                @endif
                <p class="font-body text-xs font-semibold uppercase tracking-wider" style="color: var(--warm-500);">{{ Str::of($photo->customer_name)->explode(' ')->first() }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-12">
        <x-storefront.button href="{{ route('storefront.gallery') }}" variant="ghost" icon="arrow" class="group">
            View Full Gallery
        </x-storefront.button>
    </div>
</x-storefront.section>
@endif
