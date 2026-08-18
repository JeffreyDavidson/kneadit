<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('invitations.expired.title') }}</title>
    @vite(['resources/css/storefront.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-amber-50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">
        <div class="mb-4 text-5xl">⏰</div>
        <h1 class="mb-2 text-2xl font-bold text-gray-800">{{ __('invitations.expired.heading') }}</h1>
        <p class="text-gray-600">{{ __('invitations.expired.message') }}</p>
    </div>
</body>
</html>
