<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In | KneadIt</title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth-container">
    <div class="auth-brand"><a href="/">KneadIt</a></div>
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="subtitle">Sign in to manage your bakery.</p>

        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="/register">Create one</a>
        </div>
    </div>
</div>
</body>
</html>
