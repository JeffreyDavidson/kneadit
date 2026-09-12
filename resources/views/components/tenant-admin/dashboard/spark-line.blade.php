@props([
    'bars',
    'height' => 36,
    'color' => 'var(--pw-card-accent)',
    'fill' => null,
])

@php
    $count = count($bars);
    $viewWidth = 100;
    $points = [];

    if ($count > 1) {
        foreach ($bars as $i => $value) {
            $x = round(($i / ($count - 1)) * $viewWidth, 2);
            $y = round($height - ($value / 100 * ($height - 2)), 2);
            $points[] = "{$x},{$y}";
        }
    }

    $polyPoints = implode(' ', $points);
    $areaPoints = $points === [] ? '' : "0,{$height} " . $polyPoints . " {$viewWidth},{$height}";
@endphp

<svg
    {{ $attributes->merge(['class' => 'pw-spark-line']) }}
    width="100%"
    height="{{ $height }}"
    viewBox="0 0 {{ $viewWidth }} {{ $height }}"
    preserveAspectRatio="none"
    style="display: block"
>
    @if (count($points) > 1)
        @if ($fill)
            <polygon fill="{{ $fill }}" points="{{ $areaPoints }}" />
        @endif
        <polyline
            fill="none"
            stroke="{{ $color }}"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            vector-effect="non-scaling-stroke"
            points="{{ $polyPoints }}"
        />
    @endif
</svg>
