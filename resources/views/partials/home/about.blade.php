@php
    $aboutUs = \App\Models\Setting::get('about_us_text');
@endphp
@if($aboutUs)
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-4xl mx-auto">
        <div class="section-divider mb-16"></div>
        <div class="relative pl-12 md:pl-20">
            <span class="absolute top-0 left-0 font-display font-bold leading-none" style="font-size: clamp(4rem, 8vw, 7rem); color: var(--warm-500); opacity: 0.25;">&ldquo;</span>
            <p class="text-xl md:text-2xl leading-relaxed font-display" style="color: var(--warm-800);">
                {{ $aboutUs }}
            </p>
            <div class="mt-8">
                <a href="{{ route('storefront.about') }}" class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide uppercase transition-colors hover:underline" style="color: var(--warm-600); letter-spacing: 0.1em;">
                    Our Story
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endif
