@props([
    'label',
    'percentage',
    'count',
    'color' => 'bg-(--kn-honey)',
    'labelWidth' => 'w-8',
    'labelAlign' => 'text-right',
    'countWidth' => 'w-8',
    'countAlign' => '',
    'countSuffix' => null,
])
<div class="flex items-center gap-2 text-sm">
    <span class="{{ $labelWidth }} {{ $labelAlign }}">{{ $label }}</span>
    <div class="h-4 flex-1 overflow-hidden rounded-full bg-(--kn-surface-hover)">
        <div class="{{ $color }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
    </div>
    <span class="{{ $countWidth }} text-(--kn-muted) {{ $countAlign }}">{{ $countSuffix ?? $count }}</span>
</div>
