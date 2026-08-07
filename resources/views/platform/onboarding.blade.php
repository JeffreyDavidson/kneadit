<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Set Up Your Bakery — KneadIt</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}" />
    @include('partials.fathom')
</head>
<body>
    <div class="card">
        <h1>Set Up Your Bakery</h1>
        <p class="subtitle">Let's get your store ready. This only takes a minute.</p>

        <form method="POST" action="{{ route('onboarding.store') }}" id="onboarding-form">
            @csrf

            <label for="store_name">Bakery Name</label>
            <input
                type="text"
                id="store_name"
                name="store_name"
                placeholder="e.g. Sweet Dreams Bakery"
                value="{{ old('store_name', $bakeryName) }}"
                required
            />
            @error('store_name')
                <p class="error">{{ $message }}</p>
            @enderror
            <div class="hint">This is what your customers will see.</div>

            <label for="subdomain">Choose Your URL</label>
            <input
                type="text"
                id="subdomain"
                name="subdomain"
                placeholder="e.g. sweet-dreams"
                value="{{ old('subdomain') }}"
                required
                oninput="
                    document.getElementById('preview').textContent = this.value
                        ? this.value.toLowerCase().replace(/[^a-z0-9-]/g, '') + '.getkneadit.app'
                        : 'your-bakery.getkneadit.app'
                "
            />
            @error('subdomain')
                <p class="error">{{ $message }}</p>
            @enderror
            <div class="subdomain-preview" id="preview">your-bakery.getkneadit.app</div>

            <div class="divider"></div>

            <label>Do you need a storefront?</label>
            <div class="choice-group">
                <label class="choice-option active" id="choice-kneadit" onclick="selectChoice('kneadit')">
                    <input type="radio" name="storefront_choice" value="kneadit" checked />
                    <div class="choice-icon">🏪</div>
                    <div class="choice-label">Use KneadIt</div>
                    <div class="choice-desc">We'll build you a storefront customers can order from</div>
                </label>
                <label class="choice-option" id="choice-own" onclick="selectChoice('own')">
                    <input type="radio" name="storefront_choice" value="own" />
                    <div class="choice-icon">🌐</div>
                    <div class="choice-label">I Have My Own</div>
                    <div class="choice-desc">Just give me the admin tools — I already have a website</div>
                </label>
            </div>

            <div id="external-fields" class="external-fields hidden">
                <label for="external_website">Your Website URL</label>
                <input
                    type="url"
                    id="external_website"
                    name="external_website"
                    placeholder="https://mybakery.com"
                    value="{{ old('external_website') }}"
                />
                @error('external_website')
                    <p class="error">{{ $message }}</p>
                @enderror
                <div class="hint">We'll redirect visitors from your KneadIt URL to your site.</div>
            </div>

            <button type="submit">Create My Bakery →</button>
        </form>
    </div>

    <script @cspnonce>
        function selectChoice(choice) {
            document.getElementById('choice-kneadit').classList.toggle('active', choice === 'kneadit');
            document.getElementById('choice-own').classList.toggle('active', choice === 'own');
            document.getElementById('external-fields').classList.toggle('hidden', choice === 'kneadit');

            // Make external_website required only when "own" is selected
            document.getElementById('external_website').required = choice === 'own';
        }
    </script>
</body>
</html>
