@props([])

<div class="overflow-x-auto">
    <table {{ $attributes->class(['w-full border-collapse']) }}>
        {{ $slot }}
    </table>
</div>
