@php
    $socialLinks = $settings->homepage->socialMediaLinks;
    $storeName = $settings->store->name;
@endphp
@if (! empty(array_filter($socialLinks ?? [])))
    <section class="bg-warm-200 px-4 py-16">
        <div class="mx-auto max-w-3xl text-center">
            <p class="font-script text-warm-600 mb-6 text-xl">Follow {{ $storeName }}</p>
            <x-storefront.social-links
                :links="$socialLinks"
                class="justify-center"
                link-class="bg-warm-900 text-warm-400 hover:scale-110 hover:shadow-lg"
            />
        </div>
    </section>
@endif
