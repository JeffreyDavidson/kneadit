<section class="biscotto-about-hero">
    <h1>Our <em>Story</em></h1>
    <p>The heart behind every loaf.</p>
</section>

<section class="biscotto-about-story" id="about">
    <div class="biscotto-torn-edge biscotto-torn-edge-top" aria-hidden="true"></div>
    <div class="biscotto-about-inner">
        <figure class="biscotto-about-photo">
            <div>
                <img
                    src="{{ $settings->store->photo ? Storage::url($settings->store->photo) : $settings->heroImageUrl() }}"
                    alt="Cassie, baker and owner of {{ $settings->store->name }}"
                />
            </div>
            <figcaption>That's me! ↑</figcaption>
        </figure>

        <div class="biscotto-about-copy">
            <p class="biscotto-about-kicker">The baker behind the bread</p>
            <h2>Meet Cassie</h2>
            <div class="biscotto-about-rule" aria-hidden="true"></div>

            @if ($settings->branding->aboutUsText)
                @foreach (preg_split('/\n+/', $settings->branding->aboutUsText) ?: [] as $paragraph)
                    @if (trim($paragraph))
                        <p>{{ trim($paragraph) }}</p>
                    @endif
                @endforeach
            @else
                <p>
                    I've always loved being in the kitchen, but bread changed everything. It started with wanting my
                    family to have bread made without processed ingredients and preservatives. Curiosity took over, and
                    that's when sourdough found me.
                </p>
                <p>
                    What began as care packages for friends grew into Bakery on Biscotto. Nothing leaves this kitchen
                    that I wouldn't put on our own family table.
                </p>
            @endif

            <p>
                My approach is simple: good ingredients, good technique, and something genuinely good to eat. Baking is
                my art form, and every piece is made by hand, with care.
            </p>
            <p class="biscotto-about-signature">With love and flour dust, Cassie ✨</p>
        </div>
    </div>
    <div class="biscotto-torn-edge biscotto-torn-edge-bottom" aria-hidden="true"></div>
</section>

<section class="biscotto-about-closing">
    <p>Made in a home kitchen in Davenport, Florida</p>
    <h2>From our table to yours.</h2>
    <a href="{{ route('storefront.menu') }}">Explore Our Menu <span aria-hidden="true">→</span></a>
</section>
