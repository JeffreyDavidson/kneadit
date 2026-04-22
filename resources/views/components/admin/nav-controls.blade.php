@props(['label' => '', 'prevClick' => null, 'nextClick' => null, 'prevLabel' => '←', 'nextLabel' => '→'])

<div class="flex items-center justify-between mb-4">
    @if ($prevClick)
        <button wire:click="{{ $prevClick }}" class="cursor-pointer rounded-lg px-3 py-1.5 text-[0.8rem] text-brand-700 bg-brand-50 border border-brand-300/30">{{ $prevLabel }}</button>
    @else
        <div></div>
    @endif
    <span class="font-semibold text-brand-900 text-[0.95rem]">{{ $label }}</span>
    @if ($nextClick)
        <button wire:click="{{ $nextClick }}" class="cursor-pointer rounded-lg px-3 py-1.5 text-[0.8rem] text-brand-700 bg-brand-50 border border-brand-300/30">{{ $nextLabel }}</button>
    @else
        <div></div>
    @endif
</div>
