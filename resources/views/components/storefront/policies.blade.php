@props(['settings'])

<!-- Policies -->
@php
    $showPolicies = $settings->policies->showOnStorefront;
    $policies = $showPolicies ? array_filter([
        'Cancellation Policy' => $settings->policies->cancellation,
        'Deposit Policy' => $settings->policies->deposit,
        'Refund Policy' => $settings->policies->refund,
        'Pickup Policy' => $settings->policies->pickup,
        'Additional Terms' => $settings->policies->additionalTerms,
    ]) : [];
@endphp

@if (! empty($policies))
    <section class="bg-warm-50 border-warm-200 border-t py-12">
        <div class="mx-auto max-w-4xl px-4">
            <h3 class="font-display text-warm-700 mb-8 text-center text-2xl">Policies & Terms</h3>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($policies as $label => $text)
                    <div class="card p-5">
                        <h4 class="font-display text-warm-600 mb-2 text-lg">{{ $label }}</h4>
                        <p class="text-warm-700 text-sm leading-relaxed">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
