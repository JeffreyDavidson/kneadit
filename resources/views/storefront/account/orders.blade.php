@php
    /** @var App\Models\Customers\CateringInquiry $inquiry */
    /** @var bool $paid */
@endphp
<x-layouts.storefront>
    <section class="bg-warm-50 flex min-h-[60vh] items-center px-4 py-20">
        <div class="card mx-auto max-w-xl p-10 text-center">
            @if ($paid)
                <h1 class="font-display text-warm-900 mb-3 text-3xl">Deposit received — you're booked!</h1>
                <p class="text-warm-600 mb-6">
                    Thank you, {{ $inquiry->customer_name }}. We've confirmed your event for {{ optional($inquiry->event_date)->format('l, F j, Y') }} and
                    will be in touch soon with prep details.
                </p>
                <p class="text-warm-500 text-sm">Receipt: <code>{{ $inquiry->deposit_reference }}</code></p>
            @else
                <h1 class="font-display text-warm-900 mb-3 text-2xl">Verifying payment…</h1>
                <p class="text-warm-600">
                    If your card was charged but you don't see a confirmation in a minute, please reply to your quote
                    email or contact us directly.
                </p>
            @endif
        </div>
    </section>
</x-layouts.storefront>
