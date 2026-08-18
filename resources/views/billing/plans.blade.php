<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Your Plan — {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/billing.css') }}">
@include('partials.fathom')
</head>
<body>
    @session('success')
        <div class="success-banner">{{ $value }}</div>
    @endsession

    <div class="container">
        <div class="header">
            <h1>Choose Your Plan</h1>
            <p>All plans include a {{ config('kneadit.trial_days') }}-day free trial. No credit card required to start.</p>
        </div>

        <div class="plans-grid">
            @foreach (config('kneadit.plans') as $key => $plan)
                <div class="plan-card {{ $key === 'growth' ? 'featured' : '' }}">
                    @if ($key === 'growth')
                        <div class="badge">Most Popular</div>
                    @endif

                    <div class="plan-name">{{ $plan['name'] }}</div>
                    <div class="plan-desc">{{ $plan['description'] }}</div>

                    <div class="plan-price">
                        <span class="price-amount">${{ number_format($plan['founding_price_monthly'] / 100) }}</span>
                        <span class="price-period">/month</span>
                        @if ($plan['founding_price_monthly'] < $plan['regular_price_monthly'])
                            <div class="price-regular">${{ number_format($plan['regular_price_monthly'] / 100) }}/mo regular</div>
                            <div class="price-founding">🔒 Founding member rate — locked in forever</div>
                        @endif
                    </div>

                    <ul class="features">
                        @foreach ($plan['features'] as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    @if ($currentPlan === $key)
                        <span class="plan-btn plan-btn-current">Current Plan</span>
                    @elseif ($currentPlan)
                        <form action="{{ route('billing.swap', $key) }}" method="POST">
                            @csrf
                            <button type="submit" class="plan-btn {{ $key === 'growth' ? 'plan-btn-primary' : 'plan-btn-outline' }}">
                                Switch to {{ $plan['name'] }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('billing.checkout', $key) }}" method="POST">
                            @csrf
                            <button type="submit" class="plan-btn {{ $key === 'growth' ? 'plan-btn-primary' : 'plan-btn-outline' }}">
                                Start Free Trial
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="trial-note">
            <strong>{{ config('kneadit.trial_days') }}-day free trial</strong> on all plans. Cancel anytime. No questions asked.
        </div>

        @if ($currentPlan)
            <div class="portal-link">
                <a href="{{ route('billing.portal') }}">Manage billing & invoices →</a>
            </div>
        @endif
    </div>
</body>
</html>
