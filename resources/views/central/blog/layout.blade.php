<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Blog — KneadIt')</title>
    <meta
        name="description"
        content="@yield('meta_description', 'Guides, tips, and resources for cottage food bakers. Learn about cottage food laws, pricing strategies, and growing your home bakery business.')"
    />
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')" />
    @endif
    <link rel="alternate" type="application/rss+xml" title="KneadIt Resources" href="{{ route('blog.feed') }}" />
    <meta property="og:title" content="@yield('title', 'Blog — KneadIt')" />
    <meta property="og:description" content="@yield('meta_description', 'Resources for cottage food bakers')" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="@yield('og_image', asset('og.svg'))" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="icon" href="/images/logo-icon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}" />
    @include('partials.fathom')
</head>
<body>
    <nav class="site-nav">
        <a href="{{ route('home') }}" class="nav-brand">KneadIt</a>
        <div class="nav-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('home') }}#features">Features</a>
            <a href="{{ route('home') }}#pricing">Pricing</a>
            <a href="{{ route('home') }}#contact">Contact</a>
            <a href="{{ route('blog.index') }}">Resources</a>
            <a href="{{ route('register') }}" class="nav-cta">Get Started</a>
        </div>
    </nav>

    @yield('content')

    <footer class="site-footer">
        <p
            style="
                color: var(--honey);
                font-weight: 600;
                font-family: var(--font-serif);
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            "
        >
            KneadIt
        </p>
        <p>The bakery management platform for cottage food bakers.</p>
        <p style="margin-top: 1rem">
            <a href="/terms">Terms</a> · <a href="/privacy">Privacy</a> · <a href="/resources">Resources</a> ·
            <a href="/changelog">Changelog</a>
        </p>
        <p style="margin-top: 1rem; opacity: 0.4; font-size: 0.75rem">
            © {{ date('Y') }} KneadIt. All rights reserved.
        </p>
        <p style="margin-top: 0.5rem; opacity: 0.35; font-size: 0.7rem">
            KneadIt is a business management tool. Users are responsible for compliance with their state's cottage food
            laws.
        </p>
    </footer>
</body>
</html>
