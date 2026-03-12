<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Be Right Back | KneadIt</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;--honey:#d4920c;--golden:#e8b04a;--cream:#fef9ef;--white:#fff;--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif}
body{font-family:var(--font-sans);background:var(--warm-black);color:var(--cream);min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:2rem}
.wrap{max-width:480px}
.code{font-family:var(--font-serif);font-size:clamp(5rem,15vw,8rem);font-weight:700;color:var(--honey);line-height:1;margin-bottom:.5rem}
h1{font-family:var(--font-serif);font-size:1.5rem;margin-bottom:.75rem;color:var(--cream)}
p{color:var(--cinnamon);line-height:1.6;margin-bottom:1.5rem}
.dots{display:flex;gap:.5rem;justify-content:center;margin-top:1rem}
.dots span{width:8px;height:8px;background:var(--honey);border-radius:50%;animation:pulse 1.4s infinite ease-in-out}
.dots span:nth-child(2){animation-delay:.2s}
.dots span:nth-child(3){animation-delay:.4s}
@keyframes pulse{0%,80%,100%{opacity:.3;transform:scale(.8)}40%{opacity:1;transform:scale(1)}}
</style>
@include('partials.fathom')
</head>
<body>
<div class="wrap">
    <div class="code">503</div>
    <h1>Dough is rising</h1>
    <p>We're doing some quick maintenance. We'll be back in just a moment — your bakery data is safe.</p>
    <div class="dots"><span></span><span></span><span></span></div>
</div>
</body>
</html>
