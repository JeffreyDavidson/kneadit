@php
    $status = resolve(\App\Services\Stripe\StripeSettingsReader::class)->connectStatus();
@endphp

<div class="p-4">
    @if ($status === \App\Enums\Financial\StripeConnectStatus::ChargesEnabled)
        <div class="flex items-center gap-3 rounded-lg bg-(--kn-success-tint) p-4 text-(--kn-success)">
            <x-heroicon-o-check-circle class="h-6 w-6 flex-shrink-0" stroke-width="2" />
            <div>
                <p class="m-0 font-semibold">Stripe Connected</p>
                <p class="mt-1 mb-0 text-sm opacity-90">
                    Your Stripe account is connected and ready to accept payments.
                </p>
            </div>
        </div>
    @elseif ($status === \App\Enums\Financial\StripeConnectStatus::ChargesPending)
        <div class="flex items-center gap-3 rounded-lg bg-(--kn-warning-tint) p-4 text-(--kn-warning)">
            <x-heroicon-o-clock class="h-6 w-6 flex-shrink-0" stroke-width="2" />
            <div>
                <p class="m-0 font-semibold">Stripe Connected, charges not enabled yet</p>
                <p class="mt-1 mb-0 text-sm opacity-90">
                    Your Stripe account is connected, but Stripe has not enabled charges yet. If Stripe is still
                    reviewing your details this usually takes a few minutes; otherwise finish setup with Stripe.
                    <a href="{{ route('stripe.connect') }}" class="underline">Resume setup →</a>
                </p>
            </div>
        </div>
    @else
        <div class="p-6 text-center">
            <p class="m-0 mb-4 text-(--kn-muted)">
                Click the button below to connect your Stripe account. You'll be redirected to Stripe to complete setup.
                No Stripe account? One will be created for you.
            </p>
            <a
                href="{{ route('stripe.connect') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-(--kn-honey) px-6 py-3 text-[0.95rem] font-semibold text-(--kn-on-honey) no-underline"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z" />
                </svg>
                Connect with Stripe
            </a>
        </div>
    @endif
</div>
