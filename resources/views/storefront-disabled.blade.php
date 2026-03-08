<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $storeName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #fef9ef;
            color: #1c1410;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .container { max-width: 500px; padding: 3rem 2rem; }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2a1f18;
        }
        p {
            color: #8b6844;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .admin-link {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #d4920c, #e8b04a);
            color: white;
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .admin-link:hover {
            box-shadow: 0 4px 15px rgba(212, 146, 12, 0.3);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $storeName }}</h1>
        <p>This bakery manages their orders through KneadIt but has their own website for customers.</p>
        @if($tenant->external_website)
            <a href="{{ $tenant->external_website }}" class="admin-link">Visit Our Website →</a>
        @endif
    </div>
</body>
</html>
