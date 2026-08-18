<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('auth.login.title', ['app' => config('app.name')]) }}</title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth-container">
    <div class="auth-brand"><a href="/">{{ config('app.name') }}</a></div>
    <div class="auth-card">
        <h1>{{ __('auth.login.heading') }}</h1>
        <p class="subtitle">{{ __('auth.login.subtitle') }}</p>

        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label for="email">{{ __('forms.labels.email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('forms.placeholders.email') }}" required autofocus>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">{{ __('forms.labels.password') }}</label>
                <input type="password" id="password" name="password" placeholder="{{ __('forms.placeholders.password') }}" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember" @checked(old('remember'))>
                {{ __('auth.login.remember_me') }}
            </label>

            <button type="submit" class="auth-btn">{{ __('auth.login.submit') }}</button>
        </form>

        <div class="auth-footer">
            {{ __('auth.login.no_account') }} <a href="/register">{{ __('auth.login.create_one') }}</a>
        </div>
    </div>
</div>
</body>
</html>
