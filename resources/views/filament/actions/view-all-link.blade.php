@php
    $href = $action->getUrl();
    $label = $action->getLabel();
@endphp

<a href="{{ $href }}" class="text-honey text-xs no-underline">{{ $label }} →</a>
