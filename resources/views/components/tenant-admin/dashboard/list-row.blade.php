@props(['label' => null, 'value' => null, 'dotColor' => null])

{{-- Pass either `label` (plain string) or use the slot for richer
     content (linked text, mixed inline elements). When both are given,
     `label` wins. `value` renders on the right; omit it for left-only
     rows. --}}
<div {{ $attributes->class('pw-row') }}>
    <span>
        @if ($dotColor)
            <span class="pw-dot" style="background: {{ $dotColor }};"></span>
        @endif
        {{ $label ?? $slot }}
    </span>
    @if ($value !== null)
        <span>{{ $value }}</span>
    @endif
</div>
