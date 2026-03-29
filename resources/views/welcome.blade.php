<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KneadIt | Business Management for Cottage Food Bakers</title>
<meta name="description" content="Orders, invoicing, recipes, finances, storefronts — built specifically for home bakers who sell. Stop managing chaos, start baking.">
<meta property="og:title" content="KneadIt | Your Bakery, Managed">
<meta property="og:description" content="The business platform built for cottage food bakers. Orders, invoicing, recipes, storefronts — everything you need to run your home bakery.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://getkneadit.app">
<meta property="og:image" content="https://getkneadit.app/og.svg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@include('partials.fathom')
</head>
<body>

<!-- ===== STICKY NAV ===== -->
<nav class="site-nav" id="siteNav">
<a href="#hero" class="nav-brand">KneadIt</a>
<input type="checkbox" class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
<label for="navToggle" class="nav-toggle-label"><span></span></label>
<div class="nav-links">
<a href="#features">Features</a>
<a href="#story">Our Story</a>
<a href="#pricing">Pricing</a>
<a href="#contact">Contact</a>
<a href="/resources">Resources</a>
<a href="/register" class="nav-cta">Get Started</a>
</div>
</nav>

<!-- ===== 1. HERO ===== -->
<section class="hero" id="hero">
<div class="flour-wrap" id="hero-flour"></div>
<div class="hero-content">
<p class="hero-eyebrow reveal">for cottage food bakers</p>
<h1 class="hero-headline reveal reveal-d1">
Your bakery.<br>
<span class="hero-headline-input-wrap">
<input type="text" class="hero-name-input" id="bakeryNameInput" autocomplete="off" spellcheck="false" placeholder="Type your bakery name...">
</span>
</h1>
<div class="hero-type-hint" id="typeHint">
<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 14 L8 4 M4 8 L8 4 L12 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
<span>Type your bakery name above</span>
<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 14 L8 4 M4 8 L8 4 L12 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
</div>

<p class="sf-preview-label reveal reveal-d2">Here's what your customers will see</p>
<div class="storefront-preview reveal reveal-d2" id="storefrontPreview">
<div class="sf-header">
<div class="sf-logo" id="sfLogo">K</div>
<div class="sf-bakery-name" id="sfName">Your Bakery</div>
<span class="sf-badge"><span class="sf-dot"></span> Open</span>
</div>
<div class="sf-products">
<div class="sf-product"><div class="sf-product-img"></div><div class="sf-product-name">Sourdough Loaf</div><div class="sf-product-price">$8</div></div>
<div class="sf-product"><div class="sf-product-img"></div><div class="sf-product-name">Cinnamon Rolls (6)</div><div class="sf-product-price">$18</div></div>
<div class="sf-product"><div class="sf-product-img"></div><div class="sf-product-name">Focaccia</div><div class="sf-product-price">$12</div></div>
</div>
<button class="sf-order-btn">Order Now</button>
</div>

<div class="reveal reveal-d3" style="margin-top:2.5rem">
<a href="/register" class="cta-btn" style="display:inline-block;padding:.85rem 2.5rem;border-radius:14px;text-decoration:none">Start Free Trial</a>
</div>
<div class="hero-social-proof reveal reveal-d3">
<span>30-day free trial · No credit card required</span>
</div>

<div class="hero-divider reveal reveal-d4">
<!-- Hand-drawn wheat divider -->
<svg viewBox="0 0 280 50" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M140 48 C140 48 140 8 140 4" stroke="#d4920c" stroke-width="2" stroke-linecap="round"/>
<path d="M140 36 C130 30 122 22 118 14" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<path d="M140 36 C150 30 158 22 162 14" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<path d="M140 28 C128 22 120 14 117 6" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<path d="M140 28 C152 22 160 14 163 6" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<path d="M140 20 C132 16 126 10 124 4" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<path d="M140 20 C148 16 154 10 156 4" stroke="#d4920c" stroke-width="1.5" stroke-linecap="round" fill="none"/>
<ellipse cx="118" cy="12" rx="5" ry="9" transform="rotate(-25 118 12)" fill="#e8b04a" opacity=".35"/>
<ellipse cx="162" cy="12" rx="5" ry="9" transform="rotate(25 162 12)" fill="#e8b04a" opacity=".35"/>
<ellipse cx="117" cy="5" rx="4" ry="8" transform="rotate(-30 117 5)" fill="#e8b04a" opacity=".3"/>
<ellipse cx="163" cy="5" rx="4" ry="8" transform="rotate(30 163 5)" fill="#e8b04a" opacity=".3"/>
<ellipse cx="124" cy="3" rx="4" ry="7" transform="rotate(-20 124 3)" fill="#e8b04a" opacity=".25"/>
<ellipse cx="156" cy="3" rx="4" ry="7" transform="rotate(20 156 3)" fill="#e8b04a" opacity=".25"/>
<path d="M40 42 C48 32 52 20 50 10" stroke="#e8b04a" stroke-width="1.2" stroke-linecap="round" opacity=".25"/>
<path d="M240 42 C232 32 228 20 230 10" stroke="#e8b04a" stroke-width="1.2" stroke-linecap="round" opacity=".25"/>
</svg>
</div>
</div>
</section>

<!-- ===== 2. BAKER'S DAY ===== -->
<section class="bakers-day" id="bakersDay">
<div class="bakers-day-intro">
<!-- icon removed -->
<h2 class="reveal">A day with KneadIt</h2>
<p class="reveal reveal-d1">From the first light check to your evening profit smile, here's how KneadIt turns a busy day into a smooth one.</p>
<p class="day-subtitle reveal reveal-d2">Follow along as KneadIt handles your entire day.</p>
</div>
<div class="day-timeline">

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">📋</div>
<div class="day-label">Check today's orders</div>
<div class="day-ui">
<div class="day-ui-row"><span>Emma — Sourdough (2)</span><span style="font-size:var(--text-xs);color:var(--sage)">8:00 AM</span></div>
<div class="day-ui-row"><span>Marcus — Cinnamon Rolls</span><span style="font-size:var(--text-xs);color:var(--sage)">9:30 AM</span></div>
<div class="day-ui-row"><span>Priya — Focaccia + Loaf</span><span style="font-size:var(--text-xs);color:var(--sage)">10:00 AM</span></div>
<div class="day-ui-row"><span>Jake — Birthday Cake</span><span style="font-size:var(--text-xs);color:var(--sage)">12:00 PM</span></div>
<div class="day-ui-row"><span>Sarah — Dinner Rolls (24)</span><span style="font-size:var(--text-xs);color:var(--sage)">2:00 PM</span></div>
<div class="day-ui-row"><span>Liu — Bagels (12)</span><span style="font-size:var(--text-xs);color:var(--sage)">3:30 PM</span></div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">5:30 AM</span>
</div>

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">📝</div>
<div class="day-label">Auto-generated prep list</div>
<div class="day-ui">
<div class="day-ui-row"><span>🌾 All-purpose flour</span><strong>8 cups</strong></div>
<div class="day-ui-row"><span>🍬 Sugar</span><strong>3 cups</strong></div>
<div class="day-ui-row"><span>🧈 Butter</span><strong>2.5 cups</strong></div>
<div class="day-ui-row"><span>🥚 Eggs</span><strong>14</strong></div>
<div class="day-ui-row"><span>🫙 Yeast</span><strong>6 tsp</strong></div>
<div class="day-ui-row"><span>🥛 Milk</span><strong>4 cups</strong></div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">6:00 AM</span>
</div>

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">🔥</div>
<div class="day-label">Mark orders as baking</div>
<div class="day-ui">
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
<span class="day-ui-pill pill-confirmed">Confirmed</span>
<span style="color:var(--cinnamon)">→</span>
<span class="day-ui-pill pill-baking">Baking</span>
</div>
<div class="day-ui-row"><span>Emma's Sourdough</span><span class="day-ui-pill pill-baking" style="font-size:10px">Baking</span></div>
<div class="day-ui-row"><span>Marcus's Cinnamon Rolls</span><span class="day-ui-pill pill-baking" style="font-size:10px">Baking</span></div>
<div class="day-ui-row"><span>Priya's Focaccia</span><span class="day-ui-pill pill-confirmed" style="font-size:10px">Confirmed</span></div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">9:00 AM</span>
</div>

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">💰</div>
<div class="day-label">Invoice sent automatically</div>
<div class="day-ui" style="text-align:center;padding:1.5rem 1rem">
<div style="font-size:2rem;margin-bottom:.5rem">📩</div>
<div style="font-weight:700;color:var(--warm-black);margin-bottom:.25rem">Payment received</div>
<div class="day-ui-badge badge-paid">✓ $47.00 paid</div>
<div style="margin-top:.75rem;font-size:var(--text-xs);color:var(--cinnamon)">Invoice #1042 · Emma W.</div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">11:30 AM</span>
</div>

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">⭐</div>
<div class="day-label">Customer picks up &amp; reviews</div>
<div class="day-ui" style="text-align:center;padding:1.5rem 1rem">
<div class="day-ui-stars">★★★★★</div>
<div style="margin-top:.5rem;font-style:italic;color:var(--walnut);font-size:var(--text-sm)">"Best sourdough I've ever had! The crust is perfection. Already ordering again."</div>
<div style="margin-top:.5rem;font-weight:600;font-size:var(--text-xs)">— Emma W.</div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">2:00 PM</span>
</div>

<div class="day-entry">
<div class="day-card">
<div class="day-card-icon">📊</div>
<div class="day-label">Check today's profit</div>
<div class="day-ui">
<div class="day-ui-profit" style="justify-content:center;padding:.5rem 0">
<div class="day-ui-stat"><div class="day-ui-stat-val" style="color:var(--warm-black)">$312</div><div class="day-ui-stat-label">Revenue</div></div>
<div class="day-ui-stat"><div class="day-ui-stat-val profit-red">$89</div><div class="day-ui-stat-label">Costs</div></div>
<div class="day-ui-stat"><div class="day-ui-stat-val profit-green">$223 ↑</div><div class="day-ui-stat-label">Profit</div></div>
</div>
</div>
</div>
<div class="day-spacer"></div>
<span class="day-time-badge">8:00 PM</span>
</div>

</div>
</section>

<!-- ===== 3. THE NUMBERS ===== -->
<section class="numbers" id="numbers">
<svg class="numbers-wheat numbers-wheat-l" width="60" height="180" viewBox="0 0 60 180" fill="none"><path d="M30 180 C30 180 30 20 30 5" stroke="#e8b04a" stroke-width="2" stroke-linecap="round"/><ellipse cx="18" cy="40" rx="8" ry="18" transform="rotate(-20 18 40)" fill="#e8b04a"/><ellipse cx="42" cy="55" rx="8" ry="18" transform="rotate(20 42 55)" fill="#e8b04a"/><ellipse cx="16" cy="80" rx="7" ry="16" transform="rotate(-25 16 80)" fill="#e8b04a"/><ellipse cx="44" cy="95" rx="7" ry="16" transform="rotate(25 44 95)" fill="#e8b04a"/><ellipse cx="20" cy="120" rx="6" ry="14" transform="rotate(-15 20 120)" fill="#e8b04a"/><ellipse cx="40" cy="135" rx="6" ry="14" transform="rotate(15 40 135)" fill="#e8b04a"/></svg>
<svg class="numbers-wheat numbers-wheat-r" width="60" height="180" viewBox="0 0 60 180" fill="none"><path d="M30 180 C30 180 30 20 30 5" stroke="#e8b04a" stroke-width="2" stroke-linecap="round"/><ellipse cx="18" cy="40" rx="8" ry="18" transform="rotate(-20 18 40)" fill="#e8b04a"/><ellipse cx="42" cy="55" rx="8" ry="18" transform="rotate(20 42 55)" fill="#e8b04a"/><ellipse cx="16" cy="80" rx="7" ry="16" transform="rotate(-25 16 80)" fill="#e8b04a"/><ellipse cx="44" cy="95" rx="7" ry="16" transform="rotate(25 44 95)" fill="#e8b04a"/><ellipse cx="20" cy="120" rx="6" ry="14" transform="rotate(-15 20 120)" fill="#e8b04a"/><ellipse cx="40" cy="135" rx="6" ry="14" transform="rotate(15 40 135)" fill="#e8b04a"/></svg>
<div class="numbers-grid">
<div class="number-item reveal"><div class="number-val" data-target="50" data-suffix="+">0</div><div class="number-sub">features built for home bakers</div></div>
<div class="number-item reveal reveal-d1"><div class="number-val" data-target="100" data-suffix="%">0</div><div class="number-sub">built &amp; tested in a real kitchen</div></div>
<div class="number-item reveal reveal-d2"><div class="number-val" data-target="0" data-prefix="$" data-suffix="">0</div><div class="number-sub">to start · 30-day free trial</div></div>
</div>
</section>

<!-- ===== 4. FEATURES BENTO ===== -->
<section class="features" id="features">
<div class="features-header">
<h2 class="reveal">Everything you need.<br>Nothing you don't.</h2>
<p class="reveal reveal-d1" style="color:var(--walnut);margin-top:.75rem;font-size:var(--text-lg)">Built by a baker who got tired of spreadsheets.</p>
</div>
<div class="bento">
<!-- Large: Storefront -->
<div class="bento-card bento-large reveal">
<p>A beautiful menu your customers will love. Built-in ordering, capacity limits, no more DM chaos.</p>
<div class="bento-demo">
<div style="display:flex;gap:.35rem;margin-bottom:.75rem">
<span style="padding:3px 10px;background:var(--honey);color:var(--white);border-radius:20px;font-size:var(--text-xs);font-weight:600">All</span>
<span style="padding:3px 10px;background:var(--sourdough);border-radius:20px;font-size:var(--text-xs);color:var(--walnut)">Breads</span>
<span style="padding:3px 10px;background:var(--sourdough);border-radius:20px;font-size:var(--text-xs);color:var(--walnut)">Pastries</span>
</div>
<div class="bento-sf-grid">
<div class="bento-sf-item"><div class="bento-sf-swatch"></div><div class="bento-sf-name">Sourdough</div><div class="bento-sf-price">$8</div></div>
<div class="bento-sf-item"><div class="bento-sf-swatch"></div><div class="bento-sf-name">Cinnamon Rolls</div><div class="bento-sf-price">$18</div></div>
<div class="bento-sf-item"><div class="bento-sf-swatch"></div><div class="bento-sf-name">Focaccia</div><div class="bento-sf-price">$12</div></div>
</div>
<div style="text-align:right"><span class="bento-cart-btn">+ Add to Cart</span></div>
</div>
</div>

<!-- Large: Financial Dashboard -->
<div class="bento-card bento-large reveal reveal-d1">
<p>Know exactly what you're making. Track every dollar in, every dollar out.</p>
<div class="bento-demo">
<div class="bento-chart" id="bentoChart">
<div class="bento-bar" data-height="30"></div>
<div class="bento-bar" data-height="45"></div>
<div class="bento-bar" data-height="40"></div>
<div class="bento-bar" data-height="60"></div>
<div class="bento-bar" data-height="75"></div>
<div class="bento-bar" data-height="90"></div>
</div>
<div class="bento-chart-labels"><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span><span>Jan</span><span>Feb</span></div>
<div style="display:flex;justify-content:space-between;margin-top:.75rem;font-size:var(--text-xs)">
<span style="color:var(--sage);font-weight:700">Revenue: $4,280</span>
<span style="color:var(--berry);font-weight:600">Expenses: $1,520</span>
</div>
<div class="bento-cap"><div class="bento-cap-fill"></div></div>
<div style="font-size:var(--text-xs);color:var(--cinnamon);margin-top:.25rem">Revenue cap: $180K / $250K</div>
</div>
</div>

<!-- Medium: Pricing & Invoicing -->
<div class="bento-card bento-medium reveal reveal-d2">
<h3>Pricing &amp; Invoicing</h3>
<p>Cost your recipes, set smart prices, and send PayPal invoices, all in one flow.</p>
<div class="bento-demo">
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem">
<div style="text-align:center;flex:1">
<div style="font-size:var(--text-xs);color:var(--cinnamon);margin-bottom:.25rem">Cost per loaf</div>
<div style="font-family:var(--font-serif);font-size:var(--text-xl);font-weight:700;color:var(--honey)">$2.47</div>
<div style="font-size:var(--text-xs);color:var(--sage);font-weight:700">68% margin</div>
</div>
<div style="color:var(--cinnamon);font-size:var(--text-lg)">→</div>
<div style="text-align:center;flex:1">
<div style="font-size:var(--text-xs);color:var(--cinnamon);margin-bottom:.25rem">Invoice #1042</div>
<div style="font-family:var(--font-serif);font-size:var(--text-xl);font-weight:700">$47.00</div>
<div class="day-ui-badge badge-paid" style="font-size:var(--text-xs)">✓ Paid</div>
</div>
</div>
</div>
</div>

<!-- Medium: Order Flow -->
<div class="bento-card bento-medium reveal reveal-d3">
<h3>Order Flow</h3>
<p>From order to doorstep. Track every step along the way.</p>
<div class="bento-demo">
<div class="flow-pills">
<span class="flow-pill pill-confirmed active">Confirmed</span>
<span class="flow-arrow">→</span>
<span class="flow-pill pill-baking">Baking</span>
<span class="flow-arrow">→</span>
<span class="flow-pill pill-ready">Ready</span>
<span class="flow-arrow">→</span>
<span class="flow-pill pill-delivered">Delivered</span>
</div>
<div class="flow-anim"><div class="flow-anim-bar"></div></div>
</div>
</div>

<!-- Medium: Customer Love -->
<div class="bento-card bento-medium reveal reveal-d4">
<h3>Customer Love</h3>
<p>Birthdays, reminders, and repeat orders. All automated.</p>
<div class="bento-demo" style="display:flex;align-items:center;justify-content:center;padding:1.5rem">
<div style="display:flex;flex-direction:column;gap:.75rem;width:100%">
<div class="love-notif"><span class="love-heart">♥</span><span style="font-size:var(--text-sm);font-weight:500">Birthday discount sent!</span></div>
<div class="love-notif" style="animation-delay:2s"><span style="font-size:1.25rem">🔁</span><span style="font-size:var(--text-sm);font-weight:500">Reorder reminder: Emma W.</span></div>
</div>
</div>
</div>
</div>
</section>

<!-- ===== 5. BUILT BY A BAKER ===== -->
<section class="story" id="story">
<div class="story-inner">
<div class="story-label reveal">Our Story</div>
<div class="story-pullquote reveal reveal-d1">I kept watching my wife drown in spreadsheets, texts, and sticky notes just to sell the bread people couldn't stop ordering.</div>
<div class="story-divider reveal reveal-d2"></div>
<div class="story-body">
<p class="reveal reveal-d2">She's the baker. He's the developer. Between managing orders through texts, tracking costs in a notebook, and hoping the math worked out, they realized cottage food bakers deserved better tools.</p>
<p class="reveal reveal-d3">So they built one. Not another generic business app, but something made specifically for home bakers who pour love into every recipe and deserve real tools to grow.</p>
<p class="reveal reveal-d3"><span class="story-kneadit">That's KneadIt.</span></p>
</div>
</div>
</section>

<!-- ===== 6. PRICING ===== -->
<section class="pricing" id="pricing">
<div class="pricing-header">
<h2 class="reveal">Pricing that makes sense<br>for a home baker.</h2>
<p class="reveal reveal-d1">30-day free trial on every plan. No credit card required.</p>
</div>
<div class="founding-callout reveal">🎉 Sign up during our launch window and lock in founding member rates forever.</div>
<div class="pricing-grid">
<div class="price-card reveal">
<div class="price-tier">Starter</div>
<div class="price-desc">Just getting started</div>
<ul class="price-features">
<li>Unlimited products &amp; categories</li>
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
<div class="price-card popular reveal reveal-d1">
<span class="price-badge">Most Popular</span>
<div class="price-tier">Growth</div>
<div class="price-desc">Running your business</div>
<ul class="price-features">
<li>Everything in Starter</li>
<li>Order status emails</li>
<li>Time slot scheduling &amp; capacity limits</li>
<li>Recipe management &amp; costing</li>
<li>Shopping list &amp; baking sheet</li>
<li>Coupons &amp; discount codes</li>
<li>PayPal invoicing &amp; auto-reminders</li>
</ul>
<div class="price-amount">$19<span style="font-size:var(--text-sm);font-weight:400;color:var(--cinnamon)">/month</span></div>
<div class="price-founding"><s>$29/mo</s> · Founding rate</div>
<div style="font-size:var(--text-xs);color:var(--sage);margin-bottom:1rem;font-weight:600">✓ 30-day free trial · Cancel anytime</div>
<a href="/register" class="price-btn">Start Free Trial</a>
</div>
<div class="price-card reveal reveal-d2">
<div class="price-tier">Pro</div>
<div class="price-desc">Optimize &amp; scale</div>
<ul class="price-features">
<li>Everything in Growth</li>
<li>Financial dashboard &amp; P&amp;L</li>
<li>Revenue cap tracker</li>
<li>Profit per product analysis</li>
<li>Price suggestions</li>
<li>Weekly prep planner</li>
<li>Delivery planner &amp; route optimization</li>
<li>Product trends &amp; review analytics</li>
<li>Birthday program &amp; repeat reminders</li>
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

<!-- ===== 7. FAQ ===== -->
<section class="faq" id="faq">
<div class="faq-header">
<h2 class="reveal">Common questions</h2>
</div>
<div class="faq-list">
<div class="faq-item reveal">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">What is cottage food?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">Cottage food laws let you sell homemade food products directly to consumers from your home kitchen, no commercial kitchen required. Rules vary by state, but KneadIt helps you stay compliant with built-in revenue tracking and state-specific limits.</div></div>
</div>
<div class="faq-item reveal reveal-d1">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Do I need any special equipment?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">Nope! KneadIt runs entirely in your browser on any phone, tablet, or laptop. No special hardware, no app to install. If you can check email, you can run KneadIt.</div></div>
</div>
<div class="faq-item reveal reveal-d2">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">How does the free trial work?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">Every plan comes with a 30-day free trial, no credit card required. Pick any plan, try the full feature set, and only pay when you're ready to commit.</div></div>
</div>
<div class="faq-item reveal reveal-d3">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Can I use my own domain name?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">Yes! Pro plan members can connect a custom domain to their storefront. Starter and Growth plans get a yourname.getkneadit.app subdomain that looks great too.</div></div>
</div>
<div class="faq-item reveal reveal-d4">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">What payment methods are supported?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">KneadIt integrates with PayPal for professional invoicing with automatic payment reminders. You can also track cash and other payment methods. Your customers pay you directly. KneadIt never touches your money.</div></div>
</div>
<div class="faq-item reveal reveal-d5">
<button class="faq-q" onclick="this.parentElement.classList.toggle('open')">When does KneadIt launch?<span class="faq-icon">+</span></button>
<div class="faq-a"><div class="faq-a-inner">KneadIt is live! Sign up today and start your 30-day free trial. Early adopters lock in founding member pricing — those rates stay with you forever.</div></div>
</div>
</div>
</section>

<!-- ===== 8. CONTACT ===== -->
<section class="contact" id="contact">
<div class="contact-inner">
<div class="contact-info">
<div class="contact-header">
<h2 class="reveal">Let's talk</h2>
<p class="reveal reveal-d1">Have a question about KneadIt or wondering if it's the right fit for your bakery? We're a baker and a developer who built this from our own kitchen table. Send us a message.</p>
</div>
<div class="contact-details reveal reveal-d2">
<div class="contact-detail">
<div class="contact-detail-icon">📧</div>
<div><strong>Email us</strong>hello@getkneadit.app</div>
</div>
<div class="contact-detail">
<div class="contact-detail-icon">⏱</div>
<div><strong>Response time</strong>Usually within 24 hours</div>
</div>
<div class="contact-detail">
<div class="contact-detail-icon">🍞</div>
<div><strong>A baker &amp; a developer</strong>We built this together</div>
</div>
</div>
</div>
<div class="contact-form-wrap reveal reveal-d2">
<form class="contact-form" id="contactForm" onsubmit="return handleContact(event)">
<div class="contact-row">
<div class="contact-field">
<label for="contactName">Name</label>
<input type="text" id="contactName" placeholder="Your name" required>
</div>
<div class="contact-field">
<label for="contactEmail">Email</label>
<input type="email" id="contactEmail" placeholder="your@email.com" required>
</div>
</div>
<div class="contact-field">
<label for="contactMessage">Message</label>
<textarea id="contactMessage" placeholder="What's on your mind?" required></textarea>
</div>
<button type="submit" class="contact-submit">Send Message</button>
</form>
</div>
</div>
</section>

<!-- ===== 9. FINAL CTA + FOOTER ===== -->
<section class="final-cta" id="cta">
<div class="flour-wrap" id="footerFlour"></div>
<div class="final-cta-content">
<svg width="60" height="80" viewBox="0 0 60 80" fill="none" style="margin:0 auto;display:block;opacity:.6">
<path d="M30 78 C30 78 30 15 30 5" stroke="#e8b04a" stroke-width="2" stroke-linecap="round"/>
<ellipse cx="20" cy="25" rx="6" ry="14" transform="rotate(-20 20 25)" fill="#e8b04a" opacity=".4"/>
<ellipse cx="40" cy="35" rx="6" ry="14" transform="rotate(20 40 35)" fill="#e8b04a" opacity=".4"/>
<ellipse cx="18" cy="50" rx="5" ry="12" transform="rotate(-25 18 50)" fill="#e8b04a" opacity=".35"/>
<ellipse cx="42" cy="58" rx="5" ry="12" transform="rotate(25 42 58)" fill="#e8b04a" opacity=".35"/>
</svg>
<h2 class="reveal">Your bakery deserves this.</h2>
<p class="cta-sub reveal reveal-d1">Start your 30-day free trial today.</p>
<div class="reveal reveal-d1" style="margin-top:1.5rem">
<a href="/register" class="cta-btn" style="display:inline-block;padding:.85rem 2.5rem;border-radius:14px;text-decoration:none">Get Started Free</a>
</div>
<p class="cta-note reveal reveal-d2">No credit card required. 30-day free trial on every plan.</p>
</div>
</section>

<footer>
<div class="footer-brand">KneadIt</div>
<div class="footer-tagline">Business management for cottage food bakers</div>
<!-- social removed -->
<div class="footer-links">
<a href="/privacy">Privacy</a>
<a href="/terms">Terms</a>
<a href="#contact">Contact</a>
</div>
<div class="footer-made">© 2026 KneadIt · Created by Infinity Digital</div>
<div style="font-size:var(--text-xs);color:var(--walnut);margin-top:.5rem">KneadIt is a business management tool. Users are responsible for compliance with their state's cottage food laws.</div>
</footer>

<script>
/* === Flour particles === */
function createFlourParticles(containerId, count) {
  const wrap = document.getElementById(containerId);
  if (!wrap) return;
  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'flour-particle';
    const size = 3 + Math.random() * 4;
    const opacity = 0.12 + Math.random() * 0.25;
    const duration = 12 + Math.random() * 20;
    const delay = Math.random() * duration;
    const left = Math.random() * 100;
    const sway = -40 + Math.random() * 80;
    p.style.cssText = `width:${size}px;height:${size}px;left:${left}%;top:-10px;animation-duration:${duration}s;animation-delay:-${delay}s;--p-opacity:${opacity};--p-sway:${sway}px`;
    wrap.appendChild(p);
  }
}
createFlourParticles('hero-flour', 18);
createFlourParticles('footerFlour', 12);

/* === Sticky nav scroll === */
const nav = document.getElementById('siteNav');
const navToggle = document.getElementById('navToggle');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });
// Close mobile nav on link click
document.querySelectorAll('.nav-links a').forEach(a => {
  a.addEventListener('click', () => { navToggle.checked = false; });
});

/* === Bakery name typing effect === */
const names = ['Sweet Flour Studio', 'The Bread Basket', 'Honey & Wheat Co.', 'Rise & Shine Bakes', 'Golden Crust Co.'];
const input = document.getElementById('bakeryNameInput');
const sfName = document.getElementById('sfName');
const sfLogo = document.getElementById('sfLogo');
let nameIdx = 0, charIdx = 0, deleting = false, userTyping = false, typeTimer;

function updateStorefront(name) {
  const display = name || 'Your Bakery';
  sfName.textContent = display;
  sfLogo.textContent = display.charAt(0).toUpperCase();
}

function typeLoop() {
  if (userTyping) return;
  const name = names[nameIdx];
  if (!deleting) {
    charIdx++;
    const text = name.slice(0, charIdx);
    input.value = text;
    updateStorefront(text);
    if (charIdx >= name.length) {
      deleting = true;
      typeTimer = setTimeout(typeLoop, 1500);
      return;
    }
    typeTimer = setTimeout(typeLoop, 90 + Math.random() * 70);
  } else {
    charIdx--;
    const text = name.slice(0, charIdx);
    input.value = text;
    updateStorefront(text);
    if (charIdx <= 0) {
      deleting = false;
      nameIdx = (nameIdx + 1) % names.length;
      typeTimer = setTimeout(typeLoop, 2000);
      return;
    }
    typeTimer = setTimeout(typeLoop, 40);
  }
}
typeTimer = setTimeout(typeLoop, 1200);

const typeHint = document.getElementById('typeHint');

input.addEventListener('input', function() {
  userTyping = this.value.length > 0;
  if (userTyping) {
    clearTimeout(typeTimer);
    typeHint.classList.add('hidden');
    updateStorefront(this.value);
  } else {
    charIdx = 0; nameIdx = 0; deleting = false;
    input.placeholder = 'Type your bakery name...';
    typeHint.classList.remove('hidden');
    updateStorefront('');
    typeTimer = setTimeout(typeLoop, 600);
  }
});
input.addEventListener('focus', function() {
  clearTimeout(typeTimer);
  if (!userTyping) {
    this.value = '';
    this.placeholder = 'Type your bakery name...';
    typeHint.classList.add('hidden');
    updateStorefront('');
  }
});
input.addEventListener('blur', function() {
  if (!this.value) {
    typeHint.classList.remove('hidden');
    typeTimer = setTimeout(typeLoop, 800);
  }
});

/* === Timeline scroll reveal (Baker's Day) === */
(function() {
  const entries = document.querySelectorAll('.day-entry');
  if (!entries.length) return;
  const observer = new IntersectionObserver(function(items) {
    items.forEach(function(item) {
      if (item.isIntersecting) {
        item.target.classList.add('visible');
        observer.unobserve(item.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
  entries.forEach(function(entry) { observer.observe(entry); });
})();

/* === Count-up numbers === */
function animateNumbers() {
  document.querySelectorAll('.number-val').forEach(el => {
    const target = parseInt(el.dataset.target);
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    if (el.dataset.animated) return;
    const observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) {
        el.dataset.animated = '1';
        let start = 0;
        const duration = 1500;
        const startTime = performance.now();
        function tick(now) {
          const elapsed = now - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          const current = Math.round(eased * target);
          el.textContent = prefix + current + suffix;
          if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        observer.disconnect();
      }
    }, { threshold: 0.5 });
    observer.observe(el);
  });
}
animateNumbers();

/* === Scroll reveal === */
function initReveal() {
  const els = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  els.forEach(el => observer.observe(el));
}
initReveal();

/* === Order flow animation (synced pills + bar) === */
(function() {
  const pills = document.querySelectorAll('.flow-pill');
  const bar = document.querySelector('.flow-anim-bar');
  if (!pills.length || !bar) return;
  let idx = 0;
  function update() {
    pills.forEach(p => p.classList.remove('active'));
    pills[idx].classList.add('active');
    bar.style.width = ((idx + 1) / pills.length * 100) + '%';
    idx = (idx + 1) % pills.length;
  }
  update();
  setInterval(update, 1200);
})();

/* === Chart bars animate on scroll === */
(function() {
  const chart = document.getElementById('bentoChart');
  if (!chart) return;
  const bars = chart.querySelectorAll('.bento-bar');
  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
      bars.forEach(bar => {
        bar.style.height = bar.dataset.height + '%';
      });
      observer.disconnect();
    }
  }, { threshold: 0.3 });
  observer.observe(chart);
})();

/* === Contact form handler === */
function handleContact(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('.contact-submit');
  const name = document.getElementById('contactName').value;
  const email = document.getElementById('contactEmail').value;
  const message = document.getElementById('contactMessage').value;
  if (!name || !email || !message) return false;
  btn.disabled = true;
  btn.textContent = 'Sending...';
  fetch('/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, email, message })
  })
  .then(r => r.json())
  .then(() => {
    btn.textContent = 'Message sent! ✓';
    btn.style.background = 'var(--sage)';
    form.reset();
    setTimeout(() => { btn.textContent = 'Send Message'; btn.style.background = ''; btn.disabled = false; }, 4000);
  })
  .catch(() => {
    btn.textContent = 'Something went wrong';
    btn.style.background = 'var(--berry)';
    setTimeout(() => { btn.textContent = 'Send Message'; btn.style.background = ''; btn.disabled = false; }, 3000);
  });
  return false;
}

/* Waitlist removed — registration flow is live */
</script>
</body>
</html>
