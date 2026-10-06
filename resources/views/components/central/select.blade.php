@props([])

{{-- Wrapper inherits the user's sizing classes (e.g. w-full max-w-[400px])
     so the chevron can be absolutely positioned against the actual select
     box. The chevron is `honey-text`, so it follows the light/dark switch. --}}
<div {{ $attributes->only('class') }} style="position: relative; display: block">
    <select {{ $attributes->except('class')->merge(['class' => 'w-full bg-(--kn-surface-sunken) border border-(--kn-border) rounded-lg pl-3 pr-8 py-2 text-(--kn-ink) text-sm outline-none appearance-none focus:border-(--kn-honey-text) transition-colors']) }}>
        {{ $slot }}
    </select>
    <x-heroicon-o-chevron-down
        class="text-(--kn-honey-text)"
        stroke-width="2.5"
        style="
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0.75rem;
            height: 0.75rem;
            pointer-events: none;
        "
    />
</div>
