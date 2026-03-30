@props([
    'product',
    'variant' => 'default',
])

@php
    $name = $product->name ?? 'Product';
    $price = $product->price ?? 0;
    $description = $product->description ?? '';
    $image = $product->image_url ?? $product->image ?? null;
    $letter = strtoupper(substr($name, 0, 1));
    $formattedPrice = '$' . number_format($price, 2);
@endphp

@if ($variant === 'featured')
<div
    class="group grid grid-cols-1 md:grid-cols-2 gap-0 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl"
    style="background-color: var(--warm-50); border: 1px solid var(--warm-200);"
>
    <div class="relative overflow-hidden aspect-[4/3] md:aspect-auto">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-900));">
                <span class="text-6xl font-display font-bold" style="color: var(--warm-300);">{{ $letter }}</span>
            </div>
        @endif
        <div class="absolute top-4 right-4 px-3 py-1.5 rounded-full text-sm font-bold font-body" style="background-color: var(--warm-500); color: #ffffff;">
            {{ $formattedPrice }}
        </div>
    </div>
    <div class="p-8 md:p-10 flex flex-col justify-center">
        <h3 class="font-display text-2xl md:text-3xl font-bold mb-4" style="color: var(--warm-900);">{{ $name }}</h3>
        @if ($description)
            <p class="font-body leading-relaxed" style="color: var(--warm-600);">{{ Str::limit($description, 200) }}</p>
        @endif
    </div>
</div>

@elseif ($variant === 'compact')
<div
    class="group flex items-center gap-4 p-3 rounded-xl transition-all duration-300 hover:shadow-md"
    style="background-color: var(--warm-50); border: 1px solid var(--warm-200);"
>
    <div class="relative w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-900));">
                <span class="text-lg font-display font-bold" style="color: var(--warm-300);">{{ $letter }}</span>
            </div>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="font-display font-bold truncate" style="color: var(--warm-900);">{{ $name }}</h4>
    </div>
    <span class="font-body font-bold text-sm flex-shrink-0" style="color: var(--warm-500);">{{ $formattedPrice }}</span>
</div>

@else
<div
    class="group rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
    style="background-color: var(--warm-50); border: 1px solid var(--warm-200);"
    {{ $attributes }}
>
    <div class="relative overflow-hidden aspect-[4/3]">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-900));">
                <span class="text-5xl font-display font-bold" style="color: var(--warm-300);">{{ $letter }}</span>
            </div>
        @endif
        <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-sm font-bold font-body" style="background-color: var(--warm-500); color: #ffffff;">
            {{ $formattedPrice }}
        </div>
    </div>
    <div class="p-5">
        <h3 class="font-display text-lg font-bold mb-2" style="color: var(--warm-900);">{{ $name }}</h3>
        @if ($description)
            <p class="font-body text-sm leading-relaxed" style="color: var(--warm-600);">{{ Str::limit($description, 100) }}</p>
        @endif
    </div>
</div>
@endif
