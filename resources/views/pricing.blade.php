<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pricing — KneadIt | Bakery Management for Cottage Food Bakers</title>
<meta name="description" content="Simple, transparent pricing for cottage food bakers. 30-day free trial on every plan. No credit card required. Start at $9/month.">
<meta property="og:title" content="KneadIt Pricing — Plans for Every Baker">
<meta property="og:description" content="Simple, transparent pricing. 30-day free trial. No credit card required.">
<meta property="og:url" content="https://getkneadit.app/pricing">
<link rel="canonical" href="https://getkneadit.app/pricing">
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;
--honey:#d4920c;--golden:#e8b04a;--butter:#f5d88e;--flour:#faf4e8;--cream:#fef9ef;
--white:#fff;--sage:#5a7a5a;
--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;
--text-xs:.75rem;--text-sm:.875rem;--text-base:1rem;--text-lg:1.125rem;--text-xl:1.25rem;--text-2xl:1.5rem;--text-3xl:2rem;--text-4xl:2.5rem;
}
body{font-family:var(--font-sans);background:var(--warm-black);color:var(--cream);min-height:100vh}
a{color:var(--honey);text-decoration:none;transition:color .2s}

/* Nav */
.nav{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 2rem;max-width:1200px;margin:0 auto}
.nav-logo img{height:3rem}
.nav-links{display:flex;gap:1.5rem;align-items:center;font-size:var(--text-sm)}
.nav-links a{color:var(--cinnamon);font-weight:500}
.nav-links a:hover{color:var(--honey)}
.nav-cta{background:var(--honey);color:var(--white)!important;padding:.5rem 1.25rem;border-radius:50px;font-weight:700}
.nav-cta:hover{background:var(--golden)}

/* Hero */
.pricing-hero{text-align:center;padding:4rem 1.5rem 1rem}
.pricing-hero h1{font-family:var(--font-serif);font-size:clamp(2rem,5vw,3rem);margin-bottom:.75rem}
.pricing-hero p{color:var(--cinnamon);font-size:var(--text-lg);max-width:560px;margin:0 auto}

/* Pricing */
.pricing{background:var(--cream);padding:3rem 1.5rem 5rem;border-radius:32px 32px 0 0;margin-top:2rem}
.founding-callout{max-width:640px;margin:0 auto 2.5rem;background:var(--white);border:2px solid var(--golden);border-radius:16px;padding:1.25rem 1.5rem;text-align:center;font-size:var(--text-base);color:var(--walnut)}
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;max-width:960px;margin:0 auto}
.price-card{background:var(--white);border-radius:20px;padding:2.5rem 2rem;box-shadow:0 2px 16px rgba(28,20,16,.06);transition:transform .3s,box-shadow .3s;display:flex;flex-direction:column;position:relative}
.price-card:hover{transform:translateY(-4px);box-shadow:0 8px 32px rgba(28,20,16,.12)}
.price-card.popular{border:2px solid var(--honey);box-shadow:0 4px 24px rgba(212,146,12,.15)}
.price-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--honey);color:var(--white);font-size:var(--text-xs);font-weight:700;padding:4px 16px;border-radius:50px;text-transform:uppercase;letter-spacing:.5px}
.price-tier{font-family:var(--font-serif);font-size:var(--text-xl);font-weight:700;color:var(--warm-black);margin-bottom:.25rem}
.price-desc{font-size:var(--text-sm);color:var(--cinnamon);margin-bottom:1.25rem}
.price-features{list-style:none;flex:1;margin-bottom:1.5rem}
.price-features li{padding:.35rem 0;font-size:var(--text-sm);color:var(--walnut);position:relative;padding-left:1.25rem}
.price-features li::before{content:'✓';position:absolute;left:0;color:var(--sage);font-weight:700}
.price-amount{font-family:var(--font-serif);font-size:var(--text-4xl);font-weight:700;color:var(--warm-black)}
.price-founding{font-size:var(--text-sm);color:var(--honey);margin:.25rem 0 .5rem;font-weight:600}
.price-founding s{color:var(--cinnamon);font-weight:400}
.price-btn{display:block;text-align:center;padding:.85rem;border-radius:14px;background:var(--honey);color:var(--white);font-weight:700;font-size:var(--text-base);transition:background .2s,transform .2s}
.price-btn:hover{background:var(--golden);transform:translateY(-1px);color:var(--white)}

/* FAQ */
.faq{max-width:720px;margin:0 auto;padding:4rem 1.5rem}
.faq h2{font-family:var(--font-serif);font-size:var(--text-2xl);text-align:center;margin-bottom:2rem}
.faq-item{border-bottom:1px solid var(--walnut);padding:1.25rem 0}
.faq-q{font-weight:700;color:var(--golden);margin-bottom:.5rem}
.faq-a{color:var(--cinnamon);font-size:var(--text-sm);line-height:1.7}

/* Footer */
.pricing-footer{text-align:center;padding:2rem;color:var(--cinnamon);font-size:var(--text-sm)}
.pricing-footer a{color:var(--honey)}

@media(max-width:767px){.pricing-grid{grid-template-columns:1fr;max-width:400px}.nav-links{display:none}}
</style>
@include('partials.fathom')
</head>
<body>

<nav class="nav">
    <a href="/" class="nav-logo"><img src="/images/logo-transparent.png" alt="KneadIt"></a>
    <div class="nav-links">
        <a href="/#features">Features</a>
        <a href="/resources">Resources</a>
        <a href="/changelog">Changelog</a>
        <a href="/register" class="nav-cta">Start Free Trial</a>
    </div>
</nav>

<div class="pricing-hero">
    <h1>Pricing that makes sense<br>for a home baker.</h1>
    <p>30-day free trial on every plan. No credit card required. Cancel anytime.</p>
</div>

<section class="pricing">
    <div class="founding-callout">🎉 Sign up during our launch window and lock in founding member rates forever.</div>
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
            <div class="price-amount">$9<span style="font-size:var(--text-sm);font-weight:400;color:var(--cinnamon)">/month</span></div>
            <div class="price-founding"><s>$15/mo</s> · Founding rate</div>
            <div style="font-size:var(--text-xs);color:var(--sage);margin-bottom:1rem;font-weight:600">✓ 30-day free trial · Cancel anytime</div>
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
            <div class="price-amount">$19<span style="font-size:var(--text-sm);font-weight:400;color:var(--cinnamon)">/month</span></div>
            <div class="price-founding"><s>$29/mo</s> · Founding rate</div>
            <div style="font-size:var(--text-xs);color:var(--sage);margin-bottom:1rem;font-weight:600">✓ 30-day free trial · Cancel anytime</div>
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
            <div class="price-amount">$29<span style="font-size:var(--text-sm);font-weight:400;color:var(--cinnamon)">/month</span></div>
            <div class="price-founding"><s>$45/mo</s> · Founding rate</div>
            <div style="font-size:var(--text-xs);color:var(--sage);margin-bottom:1rem;font-weight:600">✓ 30-day free trial · Cancel anytime</div>
            <a href="/register" class="price-btn">Start Free Trial</a>
        </div>
    </div>
</section>

<section class="faq">
    <h2>Frequently Asked Questions</h2>

    <div class="faq-item">
        <div class="faq-q">Do I need a credit card to start?</div>
        <div class="faq-a">No. Your 30-day free trial starts the moment you sign up — no payment info required. You'll only be asked for payment when you're ready to subscribe.</div>
    </div>

    <div class="faq-item">
        <div class="faq-q">Can I change plans later?</div>
        <div class="faq-a">Absolutely. Upgrade or downgrade anytime from your admin dashboard. Changes take effect on your next billing cycle.</div>
    </div>

    <div class="faq-item">
        <div class="faq-q">What happens when my trial ends?</div>
        <div class="faq-a">Your storefront will be paused until you subscribe, but your admin panel and all your data stay accessible. Nothing is deleted.</div>
    </div>

    <div class="faq-item">
        <div class="faq-q">What are founding member rates?</div>
        <div class="faq-a">Early adopters who sign up during our launch window lock in permanently discounted pricing. Your rate never increases as long as you stay subscribed.</div>
    </div>

    <div class="faq-item">
        <div class="faq-q">Can I cancel anytime?</div>
        <div class="faq-a">Yes, cancel anytime with one click. No contracts, no cancellation fees. Your subscription continues until the end of the current billing period.</div>
    </div>

    <div class="faq-item">
        <div class="faq-q">Do you take a cut of my sales?</div>
        <div class="faq-a">Never. KneadIt is a flat monthly subscription. We don't take any percentage of your bakery revenue. Your sales are 100% yours.</div>
    </div>
</section>

<div class="pricing-footer">
    <a href="/">← Back to KneadIt</a> · <a href="/terms">Terms</a> · <a href="/privacy">Privacy</a>
</div>

</body>
</html>
