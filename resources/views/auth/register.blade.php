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
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;
--honey:#d4920c;--golden:#e8b04a;--butter:#f5d88e;--flour:#faf4e8;--cream:#fef9ef;
--white:#fff;--sourdough:#e8dcc8;--crust:#c4956a;--berry:#a83248;--sage:#5a7a5a;
--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;
}
body{font-family:var(--font-sans);background:var(--warm-black);color:var(--cream);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
a{color:var(--honey);text-decoration:none;transition:color .2s}
a:hover{color:var(--golden)}
.auth-container{width:100%;max-width:480px}
.auth-brand{text-align:center;margin-bottom:2rem}
.auth-brand a{font-family:var(--font-serif);font-size:1.5rem;font-weight:700;color:var(--honey)}
.auth-card{background:var(--espresso);border:1px solid rgba(212,146,12,.12);border-radius:24px;padding:2.5rem;backdrop-filter:blur(8px)}
.auth-card h1{font-family:var(--font-serif);font-size:1.75rem;font-weight:700;margin-bottom:.5rem;color:var(--cream)}
.auth-card .subtitle{font-size:.875rem;color:var(--cinnamon);margin-bottom:2rem}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.875rem;font-weight:600;color:var(--golden);margin-bottom:.35rem}
.form-group input{width:100%;padding:.85rem 1.25rem;border-radius:14px;border:2px solid var(--walnut);background:rgba(28,20,16,.5);color:var(--cream);font-family:var(--font-sans);font-size:1rem;outline:none;transition:border-color .2s}
.form-group input:focus{border-color:var(--honey)}
.form-group input::placeholder{color:var(--cinnamon)}
.form-error{font-size:.8rem;color:var(--berry);margin-top:.35rem}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.auth-btn{display:block;width:100%;padding:.85rem;border-radius:14px;border:none;background:var(--honey);color:var(--white);font-family:var(--font-sans);font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s,transform .2s;margin-top:.5rem}
.auth-btn:hover{background:var(--golden);transform:translateY(-1px)}
.auth-footer{text-align:center;margin-top:1.5rem;font-size:.875rem;color:var(--cinnamon)}
@media(max-width:500px){.form-row{grid-template-columns:1fr}.auth-card{padding:1.75rem}}
</style>
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
                    <input type="checkbox" name="terms" value="1" required style="accent-color:var(--honey);margin-top:3px;min-width:16px">
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
