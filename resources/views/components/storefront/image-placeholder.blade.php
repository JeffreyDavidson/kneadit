@props([
    'name',
    'category' => null,
    'textSize' => 'text-5xl',
])

@php
    $monogram = strtoupper(mb_substr(trim((string) $name), 0, 1));
    $eyebrow = $category ? mb_strtoupper(trim((string) $category)) : '';
@endphp

{{--
    Editorial monogram placeholder, used when a tenant has not uploaded a
    product photo. Inspired by Pophams Bakery's letterpress-bookplate
    composition: cream surface, hairline inset frame, top-left eyebrow with
    leading rule, optically centered display-font monogram, and a stacked
    horizontal divider with a single brand-color asterisk as the only accent
    moment. Reads as a designed mark, not "image missing."
--}}
<div
    class="font-display relative h-full w-full overflow-hidden"
    style="background: var(--warm-100)"
    role="img"
    aria-label="{{ $name }}"
>
    {{-- Inset hairline frame — gives the placeholder the feel of a designed
         card-within-a-card without becoming a heavy boxed component. --}}
    <div
        class="pointer-events-none absolute"
        style="inset: 0.875rem; border: 1px solid var(--warm-300); border-radius: 2px"
        aria-hidden="true"
    ></div>

    {{-- Eyebrow: top-left when a category is available. Short rule plus tiny
         all-caps tag, the editorial counterpart to the centered monogram.
         Anchored left so it never collides with the price pill at top-right. --}}
    @if ($eyebrow !== '')
        <div
            class="absolute flex items-center gap-3"
            style="top: 1.5rem; left: 1.75rem; right: 1.75rem; color: var(--warm-700)"
            aria-hidden="true"
        >
            <span
                style="
                    display: inline-block;
                    flex-shrink: 0;
                    width: 1.5rem;
                    height: 1px;
                    background: currentColor;
                    opacity: 0.65;
                "
            ></span>
            <span
                class="font-body truncate font-semibold uppercase"
                style="font-size: 0.6875rem; letter-spacing: 0.22em; line-height: 1"
            >{{ $eyebrow }}</span>
        </div>
    @endif

    {{-- Monogram cluster: optically centered.
         Monogram is set in a deep warm tone (AAA on cream across all themes);
         the brand-color flourish below is the only accent moment, restrained
         to a single asterisk between two hairlines (the Pophams "stacked
         horizontal divider" rhythm, distilled to one unit). --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center" aria-hidden="true">
        <span
            class="{{ $textSize }} leading-none"
            style="color: var(--warm-700); font-weight: 500; font-feature-settings: 'liga', 'calt'"
        >{{ $monogram }}</span>

        <span class="flex items-center" style="margin-top: 1.25rem; gap: 0.625rem">
            <span style="display: inline-block; width: 1.5rem; height: 1px; background: var(--warm-300)"></span>
            <span style="color: var(--warm-500); font-size: 0.875rem; line-height: 1; font-weight: 500">&#x2733;</span>
            <span style="display: inline-block; width: 1.5rem; height: 1px; background: var(--warm-300)"></span>
        </span>
    </div>
</div>
