@php
    $status = \App\Http\Controllers\StripeConnectController::getAccountStatus();
    $connected = $status && $status['charges_enabled'];
@endphp

<div class="p-4">
    @if ($connected)
        <div class="flex items-center gap-3 p-4 bg-emerald-800 rounded-lg text-white">
            <x-heroicon-o-check-circle class="w-6 h-6 flex-shrink-0" stroke-width="2" />
            <div>
                <p class="font-semibold m-0">Stripe Connected</p>
                <p class="text-sm opacity-90 mt-1 mb-0">
                    Your Stripe account is connected and ready to accept payments.
                    @if ($status['business_profile'])
                        ({{ $status['business_profile'] }})
                    @endif
                </p>
            </div>
        </div>
    @elseif ($status && $status['details_submitted'])
        <div class="flex items-center gap-3 p-4 bg-amber-800 rounded-lg text-white">
            <x-heroicon-o-clock class="w-6 h-6 flex-shrink-0" stroke-width="2" />
            <div>
                <p class="font-semibold m-0">Verification Pending</p>
                <p class="text-sm opacity-90 mt-1 mb-0">
                    Your details have been submitted. Stripe is reviewing your account — this usually takes a few minutes.
                </p>
            </div>
        </div>
    @else
        <div class="text-center p-6">
            <p class="text-gray-400 m-0 mb-4">
                Click the button below to connect your Stripe account. You'll be redirected to Stripe to complete setup.
                No Stripe account? One will be created for you.
            </p>
            <a href="{{ route('stripe.connect') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-[#635bff] text-white rounded-lg no-underline font-semibold text-[0.95rem]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                </svg>
                Connect with Stripe
            </a>
            @if ($status)
                <p class="text-gray-500 text-[0.8rem] mt-4 mb-0">
                    Account created but setup incomplete.
                    <a href="{{ route('stripe.connect') }}" class="text-[#635bff] underline">Resume setup →</a>
                </p>
            @endif
        </div>
    @endif
</div>
