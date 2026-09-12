@props(['cols' => 3])
<div style="display: grid; grid-template-columns: repeat({{ $cols }}, 1fr); gap: 12px;" {{ $attributes }}>
    {{ $slot }}
</div>
