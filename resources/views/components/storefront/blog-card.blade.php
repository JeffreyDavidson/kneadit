@props([
    'post',
    'variant' => 'default',
])

@php
    $title = $post->title ?? 'Untitled';
    $excerpt = $post->excerpt ?? Str::limit(strip_tags($post->content ?? $post->body ?? ''), 150);
    $image = $post->featured_image ?? $post->image_url ?? $post->image ?? null;
    $date = isset($post->published_at) ? \Carbon\Carbon::parse($post->published_at)->format('M d, Y') : ($post->created_at ? $post->created_at->format('M d, Y') : '');
    $url = $post->url ?? '#';
    $letter = strtoupper(substr($title, 0, 1));
@endphp

@if($variant === 'featured')
<a href="{{ $url }}" class="group grid grid-cols-1 md:grid-cols-2 gap-0 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl" style="background-color: var(--warm-50); border: 1px solid var(--warm-200);">
    <div class="relative overflow-hidden aspect-[16/10] md:aspect-auto">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full min-h-[250px] flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-200), var(--warm-400));">
                <span class="text-6xl font-display font-bold" style="color: var(--warm-100);">{{ $letter }}</span>
            </div>
        @endif
    </div>
    <div class="p-8 md:p-10 flex flex-col justify-center">
        @if($date)
            <span class="text-xs font-body font-semibold uppercase tracking-wider mb-3" style="color: var(--warm-500);">{{ $date }}</span>
        @endif
        <h3 class="font-display text-2xl md:text-3xl font-bold mb-4 group-hover:underline decoration-2 underline-offset-4" style="color: var(--warm-900);">{{ $title }}</h3>
        @if($excerpt)
            <p class="font-body leading-relaxed" style="color: var(--warm-600);">{{ $excerpt }}</p>
        @endif
    </div>
</a>

@elseif($variant === 'compact')
<a href="{{ $url }}" class="group block p-5 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-md" style="background-color: var(--warm-50); border: 1px solid var(--warm-200);" {{ $attributes }}>
    @if($date)
        <span class="text-xs font-body font-semibold uppercase tracking-wider" style="color: var(--warm-500);">{{ $date }}</span>
    @endif
    <h4 class="font-display font-bold mt-1 mb-2 group-hover:underline decoration-1 underline-offset-2" style="color: var(--warm-900);">{{ $title }}</h4>
    @if($excerpt)
        <p class="font-body text-sm leading-relaxed line-clamp-2" style="color: var(--warm-600);">{{ $excerpt }}</p>
    @endif
</a>

@else
<a href="{{ $url }}" class="group block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background-color: var(--warm-50); border: 1px solid var(--warm-200);" {{ $attributes }}>
    <div class="relative overflow-hidden aspect-[16/10]">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-200), var(--warm-400));">
                <span class="text-5xl font-display font-bold" style="color: var(--warm-100);">{{ $letter }}</span>
            </div>
        @endif
    </div>
    <div class="p-5">
        @if($date)
            <span class="text-xs font-body font-semibold uppercase tracking-wider" style="color: var(--warm-500);">{{ $date }}</span>
        @endif
        <h3 class="font-display text-lg font-bold mt-1 mb-2 group-hover:underline decoration-1 underline-offset-2" style="color: var(--warm-900);">{{ $title }}</h3>
        @if($excerpt)
            <p class="font-body text-sm leading-relaxed line-clamp-3" style="color: var(--warm-600);">{{ $excerpt }}</p>
        @endif
    </div>
</a>
@endif
