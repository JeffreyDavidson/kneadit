<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Your Email | KneadIt</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
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
</head>
<body>
<div class="auth-container">
    <div class="auth-brand"><a href="/"><img src="/images/logo-transparent.png" alt="KneadIt" style="height:5rem;width:auto"></a></div>
    <div class="auth-card">
        <h1>Check your email</h1>
        <p>We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Click the link to verify your account.</p>

        @if (session('message'))
            <div class="success">{{ session('message') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-btn">Resend Verification Email</button>
        </form>

        <p style="margin-top:1.5rem;font-size:.875rem">
            <a href="/">Continue to KneadIt →</a>
        </p>
    </div>
</div>
</body>
</html>
