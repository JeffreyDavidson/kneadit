@props([
    'idleText',
    'loadingText',
    'loading' => 'submitting',
])

<button
    {{ $attributes->class(['py-4 rounded-full text-lg transition-all duration-300 hover:shadow-lg bg-warm-500 text-warm-900 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2']) }}
    :disabled="{{ $loading }}"
>
    <span class="spinner" x-show="{{ $loading }}" x-cloak></span>
    <span x-text="{{ $loading }} ? {{ Js::from($loadingText) }} : {{ Js::from($idleText) }}"></span>
</button>
