@php
    $status = \App\Http\Controllers\StripeConnectController::getAccountStatus();
    $connected = $status && $status['charges_enabled'];
@endphp

<div style="padding: 1rem;">
    @if ($connected)
        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #065f46; border-radius: 0.5rem; color: white;">
            <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p style="font-weight: 600; margin: 0;">Stripe Connected</p>
                <p style="font-size: 0.875rem; opacity: 0.9; margin: 0.25rem 0 0 0;">
                    Your Stripe account is connected and ready to accept payments.
                    @if ($status['business_profile'])
                        ({{ $status['business_profile'] }})
                    @endif
                </p>
            </div>
        </div>
    @elseif ($status && $status['details_submitted'])
        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #92400e; border-radius: 0.5rem; color: white;">
            <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p style="font-weight: 600; margin: 0;">Verification Pending</p>
                <p style="font-size: 0.875rem; opacity: 0.9; margin: 0.25rem 0 0 0;">
                    Your details have been submitted. Stripe is reviewing your account — this usually takes a few minutes.
                </p>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 1.5rem;">
            <p style="color: #9ca3af; margin: 0 0 1rem 0;">
                Click the button below to connect your Stripe account. You'll be redirected to Stripe to complete setup.
                No Stripe account? One will be created for you.
            </p>
            <a href="{{ route('stripe.connect') }}"
               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #635bff; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 0.95rem;">
                <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                </svg>
                Connect with Stripe
            </a>
            @if ($status)
                <p style="color: #6b7280; font-size: 0.8rem; margin: 1rem 0 0 0;">
                    Account created but setup incomplete.
                    <a href="{{ route('stripe.connect') }}" style="color: #635bff; text-decoration: underline;">Resume setup →</a>
                </p>
            @endif
        </div>
    @endif
</div>
