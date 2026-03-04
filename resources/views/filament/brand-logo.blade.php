@php
    use App\Models\Setting;
    $storeName = Setting::get('store_name', 'KneadIt');
@endphp

<div class="flex items-center gap-2">
    <span class="font-bold text-lg truncate" style="color: var(--brand-accent, #d4920c);">
        {{ $storeName }}
    </span>
</div>
