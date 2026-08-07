<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('auth.forgot_password.title', ['app' => config('app.name')]) }}</title>
    <link rel="icon" href="/images/logo-icon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
    @include('partials.fathom')
</head>
<body>
    <div class="auth-container">
        <div class="auth-brand">
            <a href="/"
                ><img
                    src="/images/logo-transparent.png"
                    alt="{{ config('app.name') }}"
                    style="height: 5rem; width: auto"
            /></a>
        </div>
        <div class="auth-card">
            <h1>{{ __('auth.forgot_password.heading') }}</h1>
            <p class="subtitle">{{ __('auth.forgot_password.subtitle') }}</p>

            @session('status')
                <div class="status-msg">{{ $value }}</div>
            @endsession

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">{{ __('forms.labels.email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('forms.placeholders.email') }}"
                        required
                        autofocus
                    />
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">{{ __('auth.forgot_password.submit') }}</button>
            </form>

            <div class="auth-footer">
                <a href="/register">{{ __('auth.forgot_password.back') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
