@php
    $href = $action->getUrl();
    $label = $action->getLabel();
@endphp

<a href="{{ $href }}" class="text-xs text-(--kn-honey-text) no-underline">{{ $label }} →</a>
