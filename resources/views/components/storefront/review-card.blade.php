@props([
    'review',
    'variant' => 'default',
    'dark' => false,
])

@php
    $rating = $review->rating ?? 5;
    $comment = $review->comment ?? $review->content ?? '';
    $name = $review->customer_name ?? $review->name ?? 'Customer';
    $cardBg = $dark ? 'var(--warm-800)' : 'var(--warm-50)';
    $cardBorder = $dark ? 'var(--warm-700)' : 'var(--warm-200)';
    $textColor = $dark ? 'var(--warm-100)' : 'var(--warm-800)';
    $mutedColor = $dark ? 'var(--warm-400)' : 'var(--warm-500)';
@endphp

@if($variant === 'featured')
<div
    class="text-center p-8 md:p-12 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
    style="background-color: {{ $cardBg }}; border: 1px solid {{ $cardBorder }};"
>
    <div class="font-script text-6xl md:text-7xl leading-none mb-4" style="color: var(--warm-500);">&ldquo;</div>
    <x-storefront.star-rating :rating="$rating" size="lg" class="justify-center mb-6" />
    <blockquote class="font-body text-lg md:text-xl leading-relaxed mb-6 max-w-2xl mx-auto italic" style="color: {{ $textColor }};">
        &ldquo;{{ $comment }}&rdquo;
    </blockquote>
    <cite class="not-italic font-body font-semibold text-sm uppercase tracking-wider" style="color: {{ $mutedColor }};">
        &mdash; {{ $name }}
    </cite>
</div>

@elseif($variant === 'compact')
<div
    class="p-4 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
    style="background-color: {{ $cardBg }}; border: 1px solid {{ $cardBorder }};"
>
    <x-storefront.star-rating :rating="$rating" size="sm" class="mb-2" />
    <p class="font-body text-sm leading-relaxed mb-2 line-clamp-2" style="color: {{ $textColor }};">
        &ldquo;{{ $comment }}&rdquo;
    </p>
    <span class="font-body text-xs font-semibold" style="color: {{ $mutedColor }};">{{ $name }}</span>
</div>

@else
<div
    class="p-6 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
    style="background-color: {{ $cardBg }}; border: 1px solid {{ $cardBorder }};"
    {{ $attributes }}
>
    <x-storefront.star-rating :rating="$rating" class="mb-4" />
    <blockquote class="font-body leading-relaxed mb-4 italic" style="color: {{ $textColor }};">
        &ldquo;{{ $comment }}&rdquo;
    </blockquote>
    <cite class="not-italic font-body font-semibold text-sm" style="color: {{ $mutedColor }};">
        &mdash; {{ $name }}
    </cite>
</div>
@endif
