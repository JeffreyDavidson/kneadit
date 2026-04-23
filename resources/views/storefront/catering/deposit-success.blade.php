@php
/** @var App\Models\Customers\CateringInquiry $inquiry */
/** @var bool $paid */
@endphp
<x-layouts.storefront>
    <section class="py-20 px-4 bg-warm-50 min-h-[60vh] flex items-center">
        <div class="max-w-xl mx-auto text-center card p-10">
            @if ($paid)
                <h1 class="font-display text-3xl text-warm-900 mb-3">Deposit received — you're booked!</h1>
                <p class="text-warm-600 mb-6">Thank you, {{ $inquiry->customer_name }}. We've confirmed your event for {{ optional($inquiry->event_date)->format('l, F j, Y') }} and will be in touch soon with prep details.</p>
                <p class="text-sm text-warm-500">Receipt: <code>{{ $inquiry->deposit_reference }}</code></p>
            @else
                <h1 class="font-display text-2xl text-warm-900 mb-3">Verifying payment…</h1>
                <p class="text-warm-600">If your card was charged but you don't see a confirmation in a minute, please reply to your quote email or contact us directly.</p>
            @endif
        </div>
    </section>
</x-layouts.storefront>
