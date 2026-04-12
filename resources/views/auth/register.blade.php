<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Your Account | KneadIt</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@include('partials.fathom')
</head>
<body>
<div class="auth-container">
    <div class="auth-brand"><a href="/"><img src="/images/logo-transparent.png" alt="KneadIt" style="height:5rem;width:auto"></a></div>
    <div class="auth-card">
        <h1>Start your bakery journey</h1>
        <p class="subtitle">Create your account and get baking in minutes.</p>

        <form method="POST" action="/register">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Your name" required autofocus>
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="bakery_name">Bakery Name</label>
                <input type="text" id="bakery_name" name="bakery_name" value="{{ old('bakery_name') }}" placeholder="Sweet Flour Studio" required>
                @error('bakery_name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 8 characters" required>
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm" required>
                </div>
            </div>

            <div class="form-group" style="margin-top:.25rem">
                <label style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;color:var(--cinnamon);font-weight:400">
                    <input type="checkbox" name="terms" value="1" required style="accent-color:var(--honey);margin-top:3px">
                    <span>I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a></span>
                </label>
                @error('terms') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? Log in at your bakery's subdomain.<br>
            <a href="/forgot-password" style="margin-top:.5rem;display:inline-block">Forgot your password?</a>
        </div>
    </div>
</div>
</body>
</html>
