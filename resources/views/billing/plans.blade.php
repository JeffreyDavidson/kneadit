<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Your Plan — KneadIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #fef9ef; color: #1c1410; min-height: 100vh; }

        .container { max-width: 1100px; margin: 0 auto; padding: 3rem 1.5rem; }
        .header { text-align: center; margin-bottom: 3rem; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #1c1410; margin-bottom: 0.5rem; }
        .header p { color: #8b6844; font-size: 1.1rem; }

        .plans-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; align-items: start; }
        @media (max-width: 768px) { .plans-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; } }

        .plan-card {
            background: white; border-radius: 1rem; padding: 2rem;
            border: 2px solid #f0e6d2; position: relative; transition: all 0.3s;
        }
        .plan-card:hover { border-color: #d4920c; box-shadow: 0 8px 30px rgba(212, 146, 12, 0.1); transform: translateY(-4px); }
        .plan-card.featured { border-color: #d4920c; box-shadow: 0 8px 30px rgba(212, 146, 12, 0.15); }

        .badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, #d4920c, #e8b04a); color: white;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
            padding: 0.3rem 1rem; border-radius: 9999px; white-space: nowrap;
        }

        .plan-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #1c1410; margin-bottom: 0.25rem; }
        .plan-desc { color: #8b6844; font-size: 0.85rem; margin-bottom: 1.5rem; }

        .plan-price { margin-bottom: 1.5rem; }
        .price-amount { font-size: 2.5rem; font-weight: 700; color: #1c1410; }
        .price-period { color: #8b6844; font-size: 0.9rem; }
        .price-regular { color: #a09080; font-size: 0.8rem; text-decoration: line-through; margin-top: 0.25rem; }
        .price-founding { color: #d4920c; font-size: 0.75rem; font-weight: 600; }

        .features { list-style: none; margin-bottom: 2rem; }
        .features li {
            padding: 0.5rem 0; font-size: 0.875rem; color: #4a3728;
            display: flex; align-items: flex-start; gap: 0.5rem;
        }
        .features li::before { content: '✓'; color: #d4920c; font-weight: 700; flex-shrink: 0; }

        .plan-btn {
            display: block; width: 100%; padding: 0.85rem; border-radius: 0.75rem;
            font-size: 0.95rem; font-weight: 600; text-align: center;
            cursor: pointer; border: none; transition: all 0.2s; text-decoration: none;
        }
        .plan-btn-primary { background: linear-gradient(135deg, #d4920c, #e8b04a); color: white; }
        .plan-btn-primary:hover { box-shadow: 0 4px 15px rgba(212, 146, 12, 0.3); transform: translateY(-1px); }
        .plan-btn-outline { background: transparent; color: #d4920c; border: 2px solid #d4920c; }
        .plan-btn-outline:hover { background: rgba(212, 146, 12, 0.05); }
        .plan-btn-current { background: #f0e6d2; color: #8b6844; cursor: default; }

        .trial-note { text-align: center; margin-top: 2rem; color: #8b6844; font-size: 0.9rem; }
        .trial-note strong { color: #d4920c; }

        .portal-link { text-align: center; margin-top: 1.5rem; }
        .portal-link a { color: #d4920c; text-decoration: underline; font-size: 0.9rem; }

        @if(session('success'))
        .success-banner {
            background: #d4920c; color: white; text-align: center;
            padding: 0.75rem; font-weight: 600; font-size: 0.9rem;
        }
        @endif
    </style>
</head>
<body>
    @if(session('success'))
        <div class="success-banner">{{ session('success') }}</div>
    @endif

    <div class="container">
        <div class="header">
            <h1>Choose Your Plan</h1>
            <p>All plans include a {{ config('saas.trial_days') }}-day free trial. No credit card required to start.</p>
        </div>

        <div class="plans-grid">
            @foreach(config('saas.plans') as $key => $plan)
                <div class="plan-card {{ $key === 'growth' ? 'featured' : '' }}">
                    @if($key === 'growth')
                        <div class="badge">Most Popular</div>
                    @endif

                    <div class="plan-name">{{ $plan['name'] }}</div>
                    <div class="plan-desc">{{ $plan['description'] }}</div>

                    <div class="plan-price">
                        <span class="price-amount">${{ number_format($plan['founding_price_monthly'] / 100) }}</span>
                        <span class="price-period">/month</span>
                        @if($plan['founding_price_monthly'] < $plan['regular_price_monthly'])
                            <div class="price-regular">${{ number_format($plan['regular_price_monthly'] / 100) }}/mo regular</div>
                            <div class="price-founding">🔒 Founding member rate — locked in forever</div>
                        @endif
                    </div>

                    <ul class="features">
                        @foreach($plan['features'] as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    @if($currentPlan === $key)
                        <span class="plan-btn plan-btn-current">Current Plan</span>
                    @elseif($currentPlan)
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
            <strong>{{ config('saas.trial_days') }}-day free trial</strong> on all plans. Cancel anytime. No questions asked.
        </div>

        @if($currentPlan)
            <div class="portal-link">
                <a href="{{ route('billing.portal') }}">Manage billing & invoices →</a>
            </div>
        @endif
    </div>
</body>
</html>
