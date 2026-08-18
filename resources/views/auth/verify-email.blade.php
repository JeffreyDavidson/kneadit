<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('auth.verify_email.title', ['app' => config('app.name')]) }}</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style @cspnonce>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;--honey:#d4920c;--golden:#e8b04a;--cream:#fef9ef;--white:#fff;--sage:#5a7a5a;--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif}
body{font-family:var(--font-sans);background:var(--warm-black);color:var(--cream);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
.auth-container{width:100%;max-width:480px}
.auth-brand{text-align:center;margin-bottom:2rem}
.auth-card{background:var(--espresso);border:1px solid rgba(212,146,12,.12);border-radius:24px;padding:2.5rem;text-align:center}
.auth-card h1{font-family:var(--font-serif);font-size:1.75rem;margin-bottom:.75rem}
.auth-card p{color:var(--cinnamon);line-height:1.6;margin-bottom:1.5rem}
.auth-btn{display:inline-block;padding:.85rem 2rem;border-radius:14px;border:none;background:var(--honey);color:var(--white);font-family:var(--font-sans);font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s}
.auth-btn:hover{background:var(--golden)}
.success{color:var(--sage);font-size:.9rem;margin-bottom:1rem}
a{color:var(--honey);text-decoration:none}
</style>
@include('partials.fathom')
</head>
<body>
<div class="auth-container">
    <div class="auth-brand"><a href="/"><img src="/images/logo-transparent.png" alt="{{ config('app.name') }}" style="height:5rem;width:auto"></a></div>
    <div class="auth-card">
        <h1>{{ __('auth.verify_email.heading') }}</h1>
        <p>{!! __('auth.verify_email.description', ['email' => e(auth()->user()->email)]) !!}</p>

        @session('message')
            <div class="success">{{ $value }}</div>
        @endsession

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-btn">{{ __('auth.verify_email.resend') }}</button>
        </form>

        <p style="margin-top:1.5rem;font-size:.875rem">
            <a href="/">{{ __('auth.verify_email.continue', ['app' => config('app.name')]) }}</a>
        </p>
    </div>
</div>
</body>
</html>
