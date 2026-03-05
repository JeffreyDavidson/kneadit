@php
    use App\Models\Setting;
    $storeName = Setting::get('store_name', 'KneadIt');
@endphp

<div style="font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: var(--brand-900, #3d2314);">
    {{ $storeName }}
</div>
