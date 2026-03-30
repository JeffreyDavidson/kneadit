<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $storeName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/storefront-disabled.css') }}">
@include('partials.fathom')
</head>
<body>
    <div class="container">
        <h1>{{ $storeName }}</h1>
        <p>This bakery manages their orders through KneadIt but has their own website for customers.</p>
        @if ($tenant->external_website)
            <a href="{{ $tenant->external_website }}" class="admin-link">Visit Our Website →</a>
        @endif
    </div>
</body>
</html>
