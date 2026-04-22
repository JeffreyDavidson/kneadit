@props([])

@php
    $arrowSvg = "url(\"data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23d4920c' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>\")";
@endphp

<select
    {{ $attributes->class([
        'w-full bg-espresso border border-honey/12 rounded-lg pl-3 pr-8 py-2 text-parchment text-sm outline-none appearance-none bg-no-repeat focus:border-honey transition-colors',
    ]) }}
    style="background-image: {{ $arrowSvg }}; background-position: right 0.75rem center;"
>
    {{ $slot }}
</select>
