@php
    $storeName = rescue(fn () => app(\App\Services\Settings\TenantSettings::class)->storeName, 'KneadIt', false);
@endphp

<div class="flex items-center h-full font-display text-2xl font-bold text-brand-900">
    {{ $storeName }}
</div>
