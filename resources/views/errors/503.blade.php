<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('errors.503.title', ['app' => config('app.name')]) }}</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/errors.css') }}">
@include('partials.fathom')
</head>
<body>
<div class="wrap">
    <div class="code">503</div>
    <h1>{{ __('errors.503.heading') }}</h1>
    <p>{{ __('errors.503.message') }}</p>
    <div class="dots"><span></span><span></span><span></span></div>
</div>
</body>
</html>
