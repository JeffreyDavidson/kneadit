<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $storeName }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/storefront-disabled.css') }}" />
    <x-analytics.fathom />
</head>
<body>
    <div class="container">
        <h1>{{ $storeName }}</h1>
        <p>We are temporarily closed and not taking orders right now. Please check back soon.</p>
    </div>
</body>
</html>
