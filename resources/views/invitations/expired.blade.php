<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('invitations.expired.title') }}</title>
    @vite(["resources/css/storefront.css"])
</head>
<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg max-w-md w-full p-8 text-center">
        <div class="text-5xl mb-4">⏰</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('invitations.expired.heading') }}</h1>
        <p class="text-gray-600">{{ __('invitations.expired.message') }}</p>
    </div>
</body>
</html>
