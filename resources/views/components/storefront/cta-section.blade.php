@props([
    'scriptText',
    'heading',
    'description' => null,
    'buttonText',
    'buttonRoute',
])

<x-storefront.dark-section padding="py-24" radial-opacity="0.08">
    <div class="text-center max-w-2xl mx-auto px-4">
        <p class="font-script text-2xl mb-4 text-warm-500">{{ $scriptText }}</p>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-{{ $description ? '6' : '8' }} text-warm-100">
            {{ $heading }}
        </h2>
        @if ($description)
            <p class="text-lg mb-10 text-warm-400">{{ $description }}</p>
        @endif
        <a href="{{ $buttonRoute }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl bg-warm-500 text-warm-900">
            {{ $buttonText }}
        </a>
    </div>
</x-storefront.dark-section>
