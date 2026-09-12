@props(['bars', 'height' => 40])

<div {{ $attributes->merge(['class' => 'pw-line', 'style' => "height: {$height}px;"]) }}>
    @foreach ($bars as $h)
        <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
    @endforeach
</div>
