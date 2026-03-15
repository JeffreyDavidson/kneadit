@props([])
<div style="overflow-x: auto;" {{ $attributes }}>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
        {{ $slot }}
    </table>
</div>
