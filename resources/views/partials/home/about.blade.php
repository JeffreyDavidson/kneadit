@php
    $aboutUs = \App\Models\Setting::get('about_us_text');
@endphp
@if($aboutUs)
<section class="py-16 px-4" style="background: var(--warm-100);">
    <div class="max-w-3xl mx-auto text-center">
        <p class="text-lg md:text-xl leading-relaxed" style="color: var(--warm-700);">
            {{ $aboutUs }}
        </p>
    </div>
</section>
@endif
