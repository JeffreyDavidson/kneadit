@props([
    'label',
    'percentage',
    'count',
    'color' => 'bg-amber-500',
    'labelWidth' => 'w-8',
    'labelAlign' => 'text-right',
    'countWidth' => 'w-8',
    'countAlign' => '',
    'countSuffix' => null,
])
<div class="flex items-center gap-2 text-sm">
    <span class="{{ $labelWidth }} {{ $labelAlign }}">{{ $label }}</span>
    <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
        <div class="{{ $color }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
    </div>
    <span class="{{ $countWidth }} text-gray-500 {{ $countAlign }}">{{ $countSuffix ?? $count }}</span>
</div>
