@php
    $socialLinks = $settings->socialMediaLinks;
    $storeName = $settings->storeName;
@endphp
@if (!empty(array_filter($socialLinks ?? [])))
<section class="py-16 px-4 bg-warm-200">
    <div class="max-w-3xl mx-auto text-center">
        <p class="font-script text-xl mb-6 text-warm-600">Follow {{ $storeName }}</p>
        <x-storefront.social-links
            :links="$socialLinks"
            class="justify-center"
            link-class="bg-warm-900 text-warm-400 hover:scale-110 hover:shadow-lg"
        />
    </div>
</section>
@endif
