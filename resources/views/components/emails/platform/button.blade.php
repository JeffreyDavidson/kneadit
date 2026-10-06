{{-- The honey-gold call-to-action button for KneadIt emails. --}}
@props(['url'])
<div style="text-align: center; margin: 28px 0">
    <a
        href="{{ $url }}"
        style="
            display: inline-block;
            background: #d4920c;
            color: #ffffff;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
        "
    >{{ $slot }}</a>
</div>
