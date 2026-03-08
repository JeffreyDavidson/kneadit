@php
    $count = $config['count'] ?? 3;
    $title = $config['title'] ?? 'Kind Words';
    $subtitle = $config['subtitle'] ?? 'What our customers say';
    $reviews = \App\Models\Review::where('is_approved', true)->latest()->take($count)->get();
@endphp
@if($reviews->count() > 0)
<x-storefront.section bg="dark" padding="xl">
    <x-storefront.section-header
        eyebrow="Testimonials"
        :title="$title"
        :subtitle="$subtitle"
        align="center"
        :dark="true"
    />

    @if($reviews->count() >= 3)
        {{-- Asymmetric: small - big - small --}}
        <div class="grid md:grid-cols-4 gap-6 items-start">
            <div class="md:col-span-1 md:mt-12">
                <x-storefront.review-card :review="$reviews[2]" variant="compact" :dark="true" />
            </div>
            <div class="md:col-span-2">
                <x-storefront.review-card :review="$reviews[0]" variant="featured" :dark="true" />
            </div>
            <div class="md:col-span-1 md:mt-12">
                <x-storefront.review-card :review="$reviews[1]" variant="compact" :dark="true" />
            </div>
        </div>
    @else
        <div class="grid md:grid-cols-{{ $reviews->count() }} gap-8">
            @foreach($reviews as $review)
                <x-storefront.review-card :review="$review" :dark="true" />
            @endforeach
        </div>
    @endif

    <div class="text-center mt-14">
        <x-storefront.button href="{{ route('storefront.reviews') }}" variant="secondary" icon="arrow" class="group" style="color: var(--warm-400); border-color: var(--warm-600);">
            Read All Reviews
        </x-storefront.button>
    </div>
</x-storefront.section>
@endif
