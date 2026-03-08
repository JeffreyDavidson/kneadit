@php
    $aboutUs = \App\Models\Setting::get('about_us_text');
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp
@if($aboutUs)
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-3xl mx-auto text-center">
        <div class="flex items-center justify-center gap-4 mb-8">
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="uppercase tracking-[0.2em] text-xs font-medium" style="color: var(--warm-500);">Our Story</span>
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.4;"></span>
        </div>
        <p class="font-display text-xl md:text-2xl leading-relaxed mb-8" style="color: var(--warm-800);">
            {{ $aboutUs }}
        </p>
        <a href="{{ route('storefront.about') }}" class="inline-flex items-center gap-2 text-sm font-medium transition-all duration-200 hover:gap-3" style="color: var(--warm-600);">
            Learn more about {{ $storeName }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>
@endif
