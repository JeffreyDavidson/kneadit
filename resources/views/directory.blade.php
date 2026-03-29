<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find a Bakery | KneadIt</title>
<meta name="description" content="Discover local cottage food bakeries on KneadIt">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/directory.css') }}">
@include('partials.fathom')
</head>
<body>

<nav class="nav">
    <a href="/" class="nav-brand">KneadIt</a>
    <div class="nav-links">
        <a href="/">Home</a>
        <a href="/directory" style="color:var(--honey)">Find a Bakery</a>
        <a href="/#pricing">Pricing</a>
        <a href="/#cta" class="nav-cta">Join Waitlist</a>
    </div>
</nav>

<section class="directory-hero">
    <h1>Find a Local Bakery</h1>
    <p>Discover amazing cottage food bakers in your area, all powered by KneadIt.</p>

    <div class="search-wrap" x-data="{ search: '' }">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" placeholder="Search bakeries..." x-model="search" @input="$dispatch('search-update', { query: search })">
    </div>
</section>

<div class="directory-content" x-data="{ search: '' }" @search-update.window="search = $event.detail.query">
    @if($bakeries->count() > 0)
        <div class="bakery-grid">
            @foreach($bakeries as $bakery)
                <div class="bakery-card" x-show="search === '' || '{{ strtolower($bakery['name']) }}'.includes(search.toLowerCase())" x-transition>
                    <div class="bakery-stripe" style="background: {{ $bakery['color'] }}"></div>
                    <div class="bakery-card-body">
                        <div class="bakery-name">{{ $bakery['name'] }}</div>
                        <a href="{{ $bakery['url'] }}" class="bakery-btn" style="background: {{ $bakery['color'] }}">Visit Store →</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <h2>🍞 Be the first baker on KneadIt!</h2>
            <p>No bakeries have set up shop yet. Start your cottage food business with KneadIt today.</p>
            <a href="/#cta" class="empty-cta">Get Started</a>
        </div>
    @endif
</div>

<footer class="directory-footer">
    <p>&copy; {{ date('Y') }} <a href="/">KneadIt</a> — Business management for cottage food bakers.</p>
</footer>

</body>
</html>
