<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Up Your Bakery — KneadIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #fef9ef; color: #1c1410; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; border-radius: 1.5rem; padding: 3rem; max-width: 540px; width: 100%; box-shadow: 0 8px 30px rgba(0,0,0,0.08); margin: 2rem; }
        h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.5rem; }
        .subtitle { color: #8b6844; margin-bottom: 2rem; }
        label { display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; color: #4a3728; }
        input[type="text"], input[type="url"] { width: 100%; padding: 0.75rem 1rem; border: 2px solid #f0e6d2; border-radius: 0.75rem; font-size: 1rem; font-family: inherit; transition: border-color 0.2s; margin-bottom: 0.5rem; }
        input:focus { outline: none; border-color: #d4920c; }
        .hint { font-size: 0.8rem; color: #8b6844; margin-bottom: 1.5rem; }
        .subdomain-preview { font-size: 0.8rem; color: #d4920c; font-weight: 600; margin-bottom: 1.5rem; }
        button { width: 100%; padding: 0.85rem; border: none; border-radius: 0.75rem; background: linear-gradient(135deg, #d4920c, #e8b04a); color: white; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        button:hover { box-shadow: 0 4px 15px rgba(212, 146, 12, 0.3); transform: translateY(-1px); }
        .error { color: #dc2626; font-size: 0.8rem; margin-bottom: 1rem; }

        /* Storefront choice */
        .choice-group { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; }
        .choice-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #f0e6d2;
            border-radius: 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .choice-option:hover { border-color: #e8b04a; }
        .choice-option.active { border-color: #d4920c; background: #fffbf0; }
        .choice-option input[type="radio"] { display: none; }
        .choice-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .choice-label { font-weight: 600; font-size: 0.9rem; color: #2a1f18; }
        .choice-desc { font-size: 0.75rem; color: #8b6844; margin-top: 0.25rem; }

        .external-fields { margin-bottom: 1.5rem; }
        .hidden { display: none; }

        .divider { height: 1px; background: #f0e6d2; margin: 1.5rem 0; }
    </style>
@include('partials.fathom')
</head>
<body>
    <div class="card">
        <h1>Set Up Your Bakery</h1>
        <p class="subtitle">Let's get your store ready. This only takes a minute.</p>

        <form method="POST" action="{{ route('onboarding.store') }}" id="onboarding-form">
            @csrf

            <label for="store_name">Bakery Name</label>
            <input type="text" id="store_name" name="store_name" placeholder="e.g. Sweet Dreams Bakery" value="{{ old('store_name', $bakeryName) }}" required>
            @error('store_name')<p class="error">{{ $message }}</p>@enderror
            <div class="hint">This is what your customers will see.</div>

            <label for="subdomain">Choose Your URL</label>
            <input type="text" id="subdomain" name="subdomain" placeholder="e.g. sweet-dreams" value="{{ old('subdomain') }}" required
                oninput="document.getElementById('preview').textContent = this.value ? this.value.toLowerCase().replace(/[^a-z0-9-]/g, '') + '.getkneadit.app' : 'your-bakery.getkneadit.app'">
            @error('subdomain')<p class="error">{{ $message }}</p>@enderror
            <div class="subdomain-preview" id="preview">your-bakery.getkneadit.app</div>

            <div class="divider"></div>

            <label>Do you need a storefront?</label>
            <div class="choice-group">
                <label class="choice-option active" id="choice-kneadit" onclick="selectChoice('kneadit')">
                    <input type="radio" name="storefront_choice" value="kneadit" checked>
                    <div class="choice-icon">🏪</div>
                    <div class="choice-label">Use KneadIt</div>
                    <div class="choice-desc">We'll build you a storefront customers can order from</div>
                </label>
                <label class="choice-option" id="choice-own" onclick="selectChoice('own')">
                    <input type="radio" name="storefront_choice" value="own">
                    <div class="choice-icon">🌐</div>
                    <div class="choice-label">I Have My Own</div>
                    <div class="choice-desc">Just give me the admin tools — I already have a website</div>
                </label>
            </div>

            <div id="external-fields" class="external-fields hidden">
                <label for="external_website">Your Website URL</label>
                <input type="url" id="external_website" name="external_website" placeholder="https://mybakery.com" value="{{ old('external_website') }}">
                @error('external_website')<p class="error">{{ $message }}</p>@enderror
                <div class="hint">We'll redirect visitors from your KneadIt URL to your site.</div>
            </div>

            <button type="submit">Create My Bakery →</button>
        </form>
    </div>

    <script>
        function selectChoice(choice) {
            document.getElementById('choice-kneadit').classList.toggle('active', choice === 'kneadit');
            document.getElementById('choice-own').classList.toggle('active', choice === 'own');
            document.getElementById('external-fields').classList.toggle('hidden', choice === 'kneadit');

            // Make external_website required only when "own" is selected
            document.getElementById('external_website').required = (choice === 'own');
        }
    </script>
</body>
</html>
