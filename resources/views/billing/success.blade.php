<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to KneadIt!</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #fef9ef; color: #1c1410; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { text-align: center; max-width: 500px; padding: 3rem; }
        .icon { font-size: 4rem; margin-bottom: 1.5rem; }
        h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.75rem; }
        p { color: #8b6844; font-size: 1rem; line-height: 1.6; margin-bottom: 2rem; }
        a { display: inline-block; background: linear-gradient(135deg, #d4920c, #e8b04a); color: white; text-decoration: none; padding: 0.85rem 2rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.2s; }
        a:hover { box-shadow: 0 4px 15px rgba(212, 146, 12, 0.3); transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🎉</div>
        <h1>Welcome to KneadIt!</h1>
        <p>Your subscription is set up and your {{ config('saas.trial_days') }}-day free trial has started. Time to get baking!</p>
        <a href="{{ route('filament.admin.pages.dashboard') }}">Go to Dashboard →</a>
    </div>
</body>
</html>
