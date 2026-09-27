@props(['label'])

<div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">{{ $label }}</dt>
    <dd {{ $attributes->class(['text-right text-[0.85rem] font-semibold text-white']) }}>{{ $slot }}</dd>
</div>
