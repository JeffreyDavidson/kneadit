@php
    $isTenant = !in_array(request()->getHost(), config('tenancy.central_domains', []));
    $storeName = $isTenant ? rescue(fn () => app(\App\Services\Settings\TenantSettings::class)->storeName, 'This Bakery', false) : 'KneadIt';
    $homeUrl = '/';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page Not Found | {{ $storeName }}</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/errors.css') }}">
@include('partials.fathom')
</head>
<body>
<div class="wrap">
    <div class="code">404</div>
    <h1>Nothing baking here</h1>
    <p>The page you're looking for doesn't exist or may have been moved.</p>
    <a href="{{ $homeUrl }}">Back to {{ $storeName }}</a>
</div>
</body>
</html>
