@props(['code'])
@use(App\Services\Settings\TenantSettings)
@php
    $isTenant = ! in_array(request()->getHost(), config('tenancy.central_domains', []));
    $storeName = $isTenant ? rescue(fn () => app(TenantSettings::class)->store->name, config('app.name'), false) : config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __("errors.{$code}.title", ['store' => $storeName]) }}</title>
    <link rel="icon" href="/images/logo-icon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}" />
    <x-analytics.fathom />
</head>
<body>
    <div class="wrap">
        <div class="code">{{ $code }}</div>
        <h1>{{ __("errors.{$code}.heading") }}</h1>
        <p>{{ __("errors.{$code}.message") }}</p>
        <a href="/">{{ __('errors.back_to', ['store' => $storeName]) }}</a>
    </div>
</body>
</html>
