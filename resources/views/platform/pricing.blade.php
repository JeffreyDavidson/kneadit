<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pricing — KneadIt | Bakery Management for Cottage Food Bakers</title>
    <meta
        name="description"
        content="Simple, transparent pricing for cottage food bakers. 30-day free trial on every plan. No credit card required. Start at $9/month."
    />
    <meta property="og:title" content="KneadIt Pricing — Plans for Every Baker" />
    <meta
        property="og:description"
        content="Simple, transparent pricing. 30-day free trial. No credit card required."
    />
    <meta property="og:url" content="https://getkneadit.app/pricing" />
    <link rel="canonical" href="https://getkneadit.app/pricing" />
    <link rel="icon" href="/images/logo-icon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}" />
    @include('partials.fathom')
</head>
<body>
    <nav class="nav">
        <a href="/" class="nav-logo"><img src="/images/logo-transparent.png" alt="KneadIt" /></a>
        <div class="nav-links">
            <a href="/#features">Features</a>
            <a href="/resources">Resources</a>
            <a href="/changelog">Changelog</a>
            <a href="/register" class="nav-cta">Start Free Trial</a>
        </div>
    </nav>

    <div class="pricing-hero">
        <h1>Pricing that makes sense<br />for a home baker.</h1>
        <p>30-day free trial on every plan. No credit card required. Cancel anytime.</p>
    </div>

    <section class="pricing">
        <div class="founding-callout">
            🎉 Sign up during our launch window and lock in founding member rates forever.
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <div class="price-tier">Starter</div>
                <div class="price-desc">Just getting started</div>
                <ul class="price-features">
                    <li>Unlimited products & categories</li>
                    <li>Order management</li>
                    <li>Storefront with online ordering</li>
                    <li>Customer directory</li>
                    <li>Order calendar</li>
                    <li>Photo gallery</li>
                </ul>
                <div class="price-amount">
                    $9<span style="font-size: var(--text-sm); font-weight: 400; color: var(--cinnamon)">/month</span>
                </div>
                <div class="price-founding"><s>$15/mo</s> · Founding rate</div>
                <div style="font-size: var(--text-xs); color: var(--sage); margin-bottom: 1rem; font-weight: 600">
                    ✓ 30-day free trial · Cancel anytime
                </div>
                <a href="/register" class="price-btn">Start Free Trial</a>
            </div>
            <div class="price-card popular">
                <span class="price-badge">Most Popular</span>
                <div class="price-tier">Growth</div>
                <div class="price-desc">Running your business</div>
                <ul class="price-features">
                    <li>Everything in Starter</li>
                    <li>Order status emails</li>
                    <li>Time slot scheduling & capacity limits</li>
                    <li>Recipe management & costing</li>
                    <li>Shopping list & baking sheet</li>
                    <li>Coupons & discount codes</li>
                    <li>PayPal invoicing & auto-reminders</li>
                </ul>
                <div class="price-amount">
                    $19<span style="font-size: var(--text-sm); font-weight: 400; color: var(--cinnamon)">/month</span>
                </div>
                <div class="price-founding"><s>$29/mo</s> · Founding rate</div>
                <div style="font-size: var(--text-xs); color: var(--sage); margin-bottom: 1rem; font-weight: 600">
                    ✓ 30-day free trial · Cancel anytime
                </div>
                <a href="/register" class="price-btn">Start Free Trial</a>
            </div>
            <div class="price-card">
                <div class="price-tier">Pro</div>
                <div class="price-desc">Optimize & scale</div>
                <ul class="price-features">
                    <li>Everything in Growth</li>
                    <li>Financial dashboard & P&L</li>
                    <li>Revenue cap tracker</li>
                    <li>Profit per product analysis</li>
                    <li>Price suggestions</li>
                    <li>Weekly prep planner</li>
                    <li>Delivery planner & route optimization</li>
                    <li>Product trends & review analytics</li>
                    <li>Birthday program & repeat reminders</li>
                    <li>Instagram caption generator</li>
                    <li>Custom branding (colors, logo)</li>
                    <li>Priority support</li>
                </ul>
                <div class="price-amount">
                    $29<span style="font-size: var(--text-sm); font-weight: 400; color: var(--cinnamon)">/month</span>
                </div>
                <div class="price-founding"><s>$45/mo</s> · Founding rate</div>
                <div style="font-size: var(--text-xs); color: var(--sage); margin-bottom: 1rem; font-weight: 600">
                    ✓ 30-day free trial · Cancel anytime
                </div>
                <a href="/register" class="price-btn">Start Free Trial</a>
            </div>
        </div>
    </section>

    <section class="faq">
        <h2>Frequently Asked Questions</h2>

        <div class="faq-item">
            <div class="faq-q">Do I need a credit card to start?</div>
            <div class="faq-a">
                No. Your 30-day free trial starts the moment you sign up — no payment info required. You'll only be
                asked for payment when you're ready to subscribe.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Can I change plans later?</div>
            <div class="faq-a">
                Absolutely. Upgrade or downgrade anytime from your admin dashboard. Changes take effect on your next
                billing cycle.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">What happens when my trial ends?</div>
            <div class="faq-a">
                Your storefront will be paused until you subscribe, but your admin panel and all your data stay
                accessible. Nothing is deleted.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">What are founding member rates?</div>
            <div class="faq-a">
                Early adopters who sign up during our launch window lock in permanently discounted pricing. Your rate
                never increases as long as you stay subscribed.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Can I cancel anytime?</div>
            <div class="faq-a">
                Yes, cancel anytime with one click. No contracts, no cancellation fees. Your subscription continues
                until the end of the current billing period.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Do you take a cut of my sales?</div>
            <div class="faq-a">
                Never. KneadIt is a flat monthly subscription. We don't take any percentage of your bakery revenue. Your
                sales are 100% yours.
            </div>
        </div>
    </section>

    <div class="pricing-footer">
        <a href="/">← Back to KneadIt</a> · <a href="/terms">Terms</a> · <a href="/privacy">Privacy</a>
    </div>
</body>
</html>
