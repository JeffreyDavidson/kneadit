@php
    use App\Models\Setting;
    $storeName = Setting::get('store_name', 'KneadIt');
@endphp

<div style="padding: 0.5rem 0;">
    <div style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #FFFFFF; line-height: 1.2; letter-spacing: -0.01em;">
        {{ $storeName }}
    </div>
    <div style="font-size: 0.65rem; font-weight: 500; color: var(--accent-gold, #d4a574); letter-spacing: 0.15em; text-transform: uppercase; margin-top: 2px;">
        Bakery Dashboard
    </div>
</div>
