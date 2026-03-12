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
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;
--honey:#d4920c;--golden:#e8b04a;--butter:#f5d88e;--flour:#faf4e8;--cream:#fef9ef;
--white:#fff;--sourdough:#e8dcc8;--crust:#c4956a;
--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;
}
html{scroll-behavior:smooth}
body{font-family:var(--font-sans);color:var(--warm-black);background:var(--cream);line-height:1.6;min-height:100vh}
a{color:inherit;text-decoration:none}

/* Nav */
.nav{background:var(--warm-black);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between}
.nav-brand{font-family:var(--font-serif);font-size:1.25rem;font-weight:700;color:var(--honey)}
.nav-links{display:flex;align-items:center;gap:1.5rem}
.nav-links a{color:var(--cream);font-size:.875rem;font-weight:500;transition:color .2s}
.nav-links a:hover{color:var(--honey)}
.nav-cta{padding:.5rem 1.25rem;border-radius:50px;background:var(--honey);color:var(--white);font-size:.875rem;font-weight:700;border:none;cursor:pointer;transition:background .2s}
.nav-cta:hover{background:var(--golden)}

/* Hero */
.directory-hero{background:linear-gradient(135deg,var(--warm-black) 0%,var(--espresso) 100%);padding:4rem 1.5rem;text-align:center}
.directory-hero h1{font-family:var(--font-serif);font-size:clamp(2rem,5vw,3.5rem);color:var(--cream);margin-bottom:.5rem}
.directory-hero p{color:var(--cinnamon);font-size:1.125rem;max-width:600px;margin:0 auto}

/* Search */
.search-wrap{max-width:500px;margin:2rem auto 0;position:relative}
.search-wrap input{width:100%;padding:.85rem 1.25rem .85rem 3rem;border-radius:14px;border:2px solid var(--walnut);background:var(--espresso);color:var(--cream);font-family:var(--font-sans);font-size:1rem;outline:none;transition:border-color .2s}
.search-wrap input:focus{border-color:var(--honey)}
.search-wrap input::placeholder{color:var(--cinnamon)}
.search-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--cinnamon);pointer-events:none}

/* Grid */
.directory-content{max-width:1100px;margin:0 auto;padding:3rem 1.5rem 5rem}
.bakery-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
@media(max-width:1023px){.bakery-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:639px){.bakery-grid{grid-template-columns:1fr}}

/* Card */
.bakery-card{background:var(--white);border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(28,20,16,.08);transition:transform .3s,box-shadow .3s}
.bakery-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(28,20,16,.12)}
.bakery-stripe{height:6px}
.bakery-card-body{padding:2rem}
.bakery-name{font-family:var(--font-serif);font-size:1.5rem;font-weight:700;margin-bottom:1.25rem;color:var(--warm-black)}
.bakery-btn{display:inline-block;padding:.7rem 1.75rem;border-radius:12px;font-weight:700;font-size:.875rem;color:var(--white);transition:opacity .2s}
.bakery-btn:hover{opacity:.85}

/* Empty */
.empty-state{text-align:center;padding:4rem 1.5rem}
.empty-state h2{font-family:var(--font-serif);font-size:1.75rem;margin-bottom:.75rem;color:var(--warm-black)}
.empty-state p{color:var(--walnut);margin-bottom:1.5rem}
.empty-cta{display:inline-block;padding:.85rem 2rem;border-radius:14px;background:var(--honey);color:var(--white);font-weight:700;transition:background .2s}
.empty-cta:hover{background:var(--golden)}

/* Footer */
.directory-footer{background:var(--warm-black);color:var(--cinnamon);padding:2rem 1.5rem;text-align:center;font-size:.875rem}
.directory-footer a{color:var(--honey)}
</style>
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
