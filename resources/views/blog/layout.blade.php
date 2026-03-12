<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Blog — KneadIt')</title>
<meta name="description" content="@yield('meta_description', 'Guides, tips, and resources for cottage food bakers. Learn about cottage food laws, pricing strategies, and growing your home bakery business.')">
@hasSection('canonical')
<link rel="canonical" href="@yield('canonical')">
@endif
<meta property="og:title" content="@yield('title', 'Blog — KneadIt')">
<meta property="og:description" content="@yield('meta_description', 'Resources for cottage food bakers')">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="@yield('og_image', 'https://getkneadit.app/og.svg')">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;
--honey:#d4920c;--golden:#e8b04a;--butter:#f5d88e;--flour:#faf4e8;--cream:#fef9ef;
--white:#fff;
--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;
}
html{scroll-behavior:smooth}
body{font-family:var(--font-sans);color:var(--warm-black);background:var(--cream);line-height:1.6}
a{color:inherit;text-decoration:none}
img{max-width:100%;display:block}

/* Nav */
.site-nav{background:var(--warm-black);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between}
.nav-brand{font-family:var(--font-serif);font-size:1.25rem;font-weight:700;color:var(--honey)}
.nav-links{display:flex;align-items:center;gap:2rem}
.nav-links a{color:var(--cream);font-size:.875rem;font-weight:500;transition:color .2s}
.nav-links a:hover{color:var(--honey)}
.nav-cta{padding:.5rem 1.25rem;border-radius:50px;background:var(--honey);color:var(--white);font-size:.875rem;font-weight:700;border:none;cursor:pointer;transition:background .2s}
.nav-cta:hover{background:var(--golden)}
@media(max-width:767px){
    .nav-links{gap:1rem}
    .nav-links a:not(.nav-cta){display:none}
}

/* Footer */
.site-footer{background:var(--warm-black);color:var(--cream);padding:3rem 2rem;text-align:center;font-size:.875rem}
.site-footer a{color:var(--honey)}
.site-footer p{opacity:.7;margin:.25rem 0}

/* Content */
.container{max-width:1100px;margin:0 auto;padding:0 1.5rem}
.prose{max-width:720px;margin:0 auto}
.prose h2{font-family:var(--font-serif);font-size:1.75rem;margin:2rem 0 .75rem;color:var(--warm-black)}
.prose h3{font-size:1.25rem;margin:1.5rem 0 .5rem;color:var(--walnut)}
.prose p{margin:0 0 1rem;color:var(--walnut);line-height:1.8}
.prose ul,.prose ol{margin:0 0 1rem;padding-left:1.5rem;color:var(--walnut)}
.prose li{margin:.25rem 0}
.prose blockquote{border-left:3px solid var(--honey);padding:.75rem 1.25rem;margin:1.5rem 0;background:var(--flour);border-radius:0 8px 8px 0;font-style:italic;color:var(--cinnamon)}
.prose img{border-radius:12px;margin:1.5rem 0}
.prose a{color:var(--honey);text-decoration:underline}

@yield('styles')
</style>
</head>
<body>

<nav class="site-nav">
    <a href="/" class="nav-brand">KneadIt</a>
    <div class="nav-links">
        <a href="/">Home</a>
        <a href="/#features">Features</a>
        <a href="/#pricing">Pricing</a>
        <a href="/#contact">Contact</a>
        <a href="/resources">Resources</a>
        <a href="/register" class="nav-cta">Get Started</a>
    </div>
</nav>

@yield('content')

<footer class="site-footer">
    <p style="color:var(--honey);font-weight:600;font-family:var(--font-serif);font-size:1.1rem;margin-bottom:.5rem">KneadIt</p>
    <p>The bakery management platform for cottage food bakers.</p>
    <p style="margin-top:1rem"><a href="/terms">Terms</a> · <a href="/privacy">Privacy</a> · <a href="/resources">Resources</a> · <a href="/changelog">Changelog</a></p>
    <p style="margin-top:1rem;opacity:.4;font-size:.75rem">© {{ date('Y') }} KneadIt. All rights reserved.</p>
</footer>

</body>
</html>
