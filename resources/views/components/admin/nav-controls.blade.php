@props(['label' => '', 'prevClick' => null, 'nextClick' => null, 'prevLabel' => '←', 'nextLabel' => '→'])
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
    @if ($prevClick)
        <button wire:click="{{ $prevClick }}" style="background: #fdf8f2; border: 1px solid rgba(212,165,116,0.3); border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; color: #6b4c3b; cursor: pointer;">{{ $prevLabel }}</button>
    @else
        <div></div>
    @endif
    <span style="font-weight: 600; color: #3d2314; font-size: 0.95rem;">{{ $label }}</span>
    @if ($nextClick)
        <button wire:click="{{ $nextClick }}" style="background: #fdf8f2; border: 1px solid rgba(212,165,116,0.3); border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; color: #6b4c3b; cursor: pointer;">{{ $nextLabel }}</button>
    @else
        <div></div>
    @endif
</div>
