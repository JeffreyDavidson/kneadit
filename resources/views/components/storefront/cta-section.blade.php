@props([
    'scriptText',
    'heading',
    'description' => null,
    'buttonText',
    'buttonRoute',
])

<x-storefront.dark-section padding="py-24" radial-opacity="0.08">
    <div class="mx-auto max-w-2xl px-4 text-center">
        <p class="font-script text-warm-500 mb-4 text-2xl">{{ $scriptText }}</p>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-{{ $description ? '6' : '8' }} text-warm-100">
            {{ $heading }}
        </h2>
        @if ($description)
            <p class="text-warm-400 mb-10 text-lg">{{ $description }}</p>
        @endif
        <a
            href="{{ $buttonRoute }}"
            class="bg-warm-500 text-warm-900 inline-block rounded-full px-10 py-4 text-lg font-semibold transition-all duration-300 hover:scale-105 hover:shadow-2xl"
        >
            {{ $buttonText }}
        </a>
    </div>
</x-storefront.dark-section>
