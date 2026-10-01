@php
    /** @var \App\Services\Settings\TenantSettings $settings */
    /** @var \App\Models\Customers\Customer $customer */
    /** @var bool $resubscribed */
    $unsubscribed = $customer->marketing_opted_out_at !== null;
    $storeName = $settings->store->name;
@endphp

<x-layouts.storefront>
    <x-storefront.hero-section
        :image="$settings->heroImageUrl()"
        image-alt="Email preferences"
        image-class="hero-img"
        min-height="40vh"
    >
        <div class="relative z-10 mx-auto max-w-2xl px-4 py-16 text-center md:py-24">
            <x-storefront.eyebrow class="hero-fade-1 mb-6">Email Preferences</x-storefront.eyebrow>
            <h1 class="font-display hero-fade-2 text-warm-100 text-3xl leading-tight font-bold md:text-5xl">
                @if ($unsubscribed)
                    {{ "You've been unsubscribed from {$storeName} marketing emails" }}
                @elseif ($resubscribed)
                    {{ "You're subscribed again" }}
                @else
                    Unsubscribe from {{ $storeName }} marketing emails?
                @endif
            </h1>
        </div>
    </x-storefront.hero-section>

    <section class="bg-warm-100 py-16 md:py-20">
        <div class="mx-auto max-w-xl px-4 text-center">
            @if ($unsubscribed)
                <p class="text-warm-700 mb-8">
                    We won't send you promotions, reminders, or other marketing emails. You'll still receive messages
                    about your orders.
                </p>
                <form method="POST" action="{{ request()->getRequestUri() }}">
                    @csrf
                    @method('DELETE')
                    <x-storefront.button type="submit" variant="outline-light" size="md">
                        Re-subscribe</x-storefront.button>
                </form>
            @elseif ($resubscribed)
                <p class="text-warm-700 mb-8">
                    Welcome back! You'll receive marketing emails from {{ $storeName }} again. You can unsubscribe at
                    any time from the link at the bottom of any of them.
                </p>
                <x-storefront.button :href="route('home')" size="md">Back to {{ $storeName }}</x-storefront.button>
            @else
                <p class="text-warm-700 mb-8">
                    You'll stop receiving promotions, reminders, and other marketing emails from {{ $storeName }}.
                    Messages about your orders will keep arriving.
                </p>
                <form method="POST" action="{{ request()->getRequestUri() }}">
                    @csrf
                    <x-storefront.button type="submit" size="md">Unsubscribe</x-storefront.button>
                </form>
            @endif
        </div>
    </section>
</x-layouts.storefront>
