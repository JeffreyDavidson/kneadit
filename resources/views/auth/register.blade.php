<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('auth.register.title', ['app' => config('app.name')]) }}</title>
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
            <h1>{{ __('auth.register.heading') }}</h1>
            <p class="subtitle">{{ __('auth.register.subtitle') }}</p>

            <form method="POST" action="/register">
                @csrf

                <div class="form-group">
                    <label for="name">{{ __('forms.labels.name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ __('forms.placeholders.name') }}"
                        required
                        autofocus
                    />
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('forms.labels.email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('forms.placeholders.email') }}"
                        required
                    />
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="bakery_name">{{ __('auth.register.bakery_name') }}</label>
                    <input
                        type="text"
                        id="bakery_name"
                        name="bakery_name"
                        value="{{ old('bakery_name') }}"
                        placeholder="{{ __('auth.register.bakery_name_placeholder') }}"
                        required
                    />
                    @error('bakery_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">{{ __('forms.labels.password') }}</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="{{ __('forms.placeholders.min_characters') }}"
                            required
                        />
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">{{ __('forms.labels.confirm_password') }}</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="{{ __('forms.placeholders.confirm') }}"
                            required
                        />
                    </div>
                </div>

                <div class="form-group" style="margin-top: 0.25rem">
                    <label
                        style="
                            display: flex;
                            align-items: flex-start;
                            gap: 0.6rem;
                            cursor: pointer;
                            color: var(--cinnamon);
                            font-weight: 400;
                        "
                    >
                        <input
                            type="checkbox"
                            name="terms"
                            value="1"
                            required
                            style="accent-color: var(--honey); margin-top: 3px"
                        />
                        <span>{!! __('auth.register.terms_agreement', ['terms_url' => '/terms', 'privacy_url' => '/privacy']) !!}</span>
                    </label>
                    @error('terms')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">{{ __('auth.register.submit') }}</button>
            </form>

            <div class="auth-footer">
                {{ __('auth.register.has_account') }}<br />
                <a
                    href="/forgot-password"
                    style="margin-top: 0.5rem; display: inline-block"
                >{{ __('auth.register.forgot_password') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
