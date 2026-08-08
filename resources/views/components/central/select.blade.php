@props([])

{{-- Wrapper inherits the user's sizing classes (e.g. w-full max-w-[400px])
     so the chevron can be absolutely positioned against the actual select
     box. Theme accent color tracks the active palette via `text-honey`,
     which is rebound to var(--accent) per theme. --}}
<div {{ $attributes->only('class') }} style="position: relative; display: block">
    <select {{ $attributes->except('class')->merge(['class' => 'w-full bg-espresso border border-honey/12 rounded-lg pl-3 pr-8 py-2 text-parchment text-sm outline-none appearance-none focus:border-honey transition-colors']) }}>
        {{ $slot }}
    </select>
    <x-heroicon-o-chevron-down
        class="text-honey"
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
