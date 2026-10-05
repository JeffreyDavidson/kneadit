<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex" />
    <title>Billing link expired — {{ config('app.name') }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}" />
    <x-analytics.fathom />
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>This billing link has expired</h1>
            <p>Billing links work once and only for a couple of minutes.</p>
        </div>

        @if ($adminUrl)
            <div class="portal-link">
                <a href="{{ $adminUrl }}">Back to your bakery</a>
                <p class="trial-note">Open Upgrade Plan there and choose Manage billing for a new link.</p>
            </div>
        @endif
    </div>
</body>
</html>
