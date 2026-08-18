@props(['label' => '', 'prevClick' => null, 'nextClick' => null, 'prevLabel' => '←', 'nextLabel' => '→'])

<div class="mb-4 flex items-center justify-between">
    @if ($prevClick)
        <button
            wire:click="{{ $prevClick }}"
            class="text-brand-700 bg-brand-50 border-brand-300/30 cursor-pointer rounded-lg border px-3 py-1.5 text-[0.8rem]"
        >
            {{ $prevLabel }}
        </button>
    @else
        <div></div>
    @endif
    <span class="text-brand-900 text-[0.95rem] font-semibold">{{ $label }}</span>
    @if ($nextClick)
        <button
            wire:click="{{ $nextClick }}"
            class="text-brand-700 bg-brand-50 border-brand-300/30 cursor-pointer rounded-lg border px-3 py-1.5 text-[0.8rem]"
        >
            {{ $nextLabel }}
        </button>
    @else
        <div></div>
    @endif
</div>
