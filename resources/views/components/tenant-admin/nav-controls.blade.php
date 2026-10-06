@props(['label' => '', 'prevClick' => null, 'nextClick' => null, 'prevLabel' => '←', 'nextLabel' => '→'])

<div class="mb-4 flex items-center justify-between">
    @if ($prevClick)
        <button
            wire:click="{{ $prevClick }}"
            class="cursor-pointer rounded-lg border border-(--kn-border-control) bg-(--kn-surface-hover) px-3 py-1.5 text-[0.8rem] font-semibold text-(--kn-ink)"
        >
            {{ $prevLabel }}
        </button>
    @else
        <div></div>
    @endif
    <span class="text-brand-50 text-[0.95rem] font-semibold">{{ $label }}</span>
    @if ($nextClick)
        <button
            wire:click="{{ $nextClick }}"
            class="cursor-pointer rounded-lg border border-(--kn-border-control) bg-(--kn-surface-hover) px-3 py-1.5 text-[0.8rem] font-semibold text-(--kn-ink)"
        >
            {{ $nextLabel }}
        </button>
    @else
        <div></div>
    @endif
</div>
