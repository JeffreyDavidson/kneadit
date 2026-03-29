<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to KneadIt!</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/billing-success.css') }}">
@include('partials.fathom')
</head>
<body>
    <div class="card">
        <div class="icon">🎉</div>
        <h1>Welcome to KneadIt!</h1>
        <p>Your subscription is set up and your {{ config('kneadit.trial_days') }}-day free trial has started. Time to get baking!</p>
        <a href="{{ route('filament.admin.pages.dashboard') }}">Go to Dashboard →</a>
    </div>
</body>
</html>
