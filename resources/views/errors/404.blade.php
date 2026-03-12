@php
    $isTenant = !in_array(request()->getHost(), config('tenancy.central_domains', []));
    $storeName = $isTenant ? (\App\Models\Setting::get('store_name', 'This Bakery') ?? 'This Bakery') : 'KneadIt';
    $homeUrl = '/';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page Not Found | {{ $storeName }}</title>
<link rel="icon" href="/images/logo-icon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--warm-black:#1c1410;--espresso:#2a1f18;--walnut:#4a3728;--cinnamon:#8b6844;
--honey:#d4920c;--golden:#e8b04a;--butter:#f5d88e;--cream:#fef9ef;--white:#fff;
--font-serif:'Playfair Display',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;
}
body{font-family:var(--font-sans);background:var(--warm-black);color:var(--cream);min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:2rem}
.wrap{max-width:480px}
.code{font-family:var(--font-serif);font-size:clamp(5rem,15vw,8rem);font-weight:700;color:var(--honey);line-height:1;margin-bottom:.5rem}
h1{font-family:var(--font-serif);font-size:1.5rem;margin-bottom:.75rem;color:var(--cream)}
p{color:var(--cinnamon);line-height:1.6;margin-bottom:1.5rem}
a{display:inline-block;padding:.75rem 2rem;border-radius:50px;background:var(--honey);color:var(--white);font-weight:700;font-size:.9rem;text-decoration:none;transition:background .2s,transform .2s}
a:hover{background:var(--golden);transform:translateY(-1px)}
</style>
</head>
<body>
<div class="wrap">
    <div class="code">404</div>
    <h1>Nothing baking here</h1>
    <p>The page you're looking for doesn't exist or may have been moved.</p>
    <a href="{{ $homeUrl }}">Back to {{ $storeName }}</a>
</div>
</body>
</html>
