<section class="biscotto-gallery-hero">
    <h1>Fresh from the Oven</h1>
    <p>A peek into our kitchen and what's baking today.</p>
</section>

<section class="biscotto-gallery-stage" x-data="galleryLightbox">
    @php
        $bakeryPhotos = $photos->count() > 0
            ? $photos->map(fn ($photo) => [
                'src' => asset('storage/customer-photos/' . basename($photo->photo_path)),
                'alt' => 'Photo by ' . $photo->customer_name,
                'caption' => $photo->caption ?: ($photo->product?->name ?? 'Fresh from the oven'),
                'author' => $photo->customer_name,
            ])
            : $products->filter(fn ($product) => filled($product->image))->map(fn ($product) => [
                'src' => Storage::url($product->image),
                'alt' => $product->name,
                'caption' => $product->name,
                'author' => $settings->store->name,
            ]);
    @endphp

    <div class="biscotto-gallery-heading">
        <span>✦</span>
        <h2>Made by Hand</h2>
        <span>✦</span>
    </div>

    @if ($bakeryPhotos->isNotEmpty())
        <div class="biscotto-gallery-grid">
            @foreach ($bakeryPhotos as $index => $photo)
                <button
                    type="button"
                    @class(['biscotto-gallery-item', 'biscotto-gallery-featured' => $index === 0])
                    @click="show(@js($photo['src']), @js($photo['caption']), @js($photo['author']))"
                >
                    <img src="{{ $photo['src'] }}" alt="{{ $photo['alt'] }}" loading="lazy" />
                    <span>{{ $photo['caption'] }}</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="biscotto-gallery-empty">
            <p>Photos coming soon&hellip;</p>
            <a href="#share-photo">Share the first one ↓</a>
        </div>
    @endif

    <div
        x-show="open"
        x-cloak
        class="biscotto-gallery-lightbox"
        @click.self="close()"
        @keydown.escape.window="close()"
        role="dialog"
        aria-label="Photo lightbox"
    >
        <button type="button" @click="close()" aria-label="Close lightbox">&times;</button>
        <figure @click.stop>
            <img x-bind:src="src" x-bind:alt="caption || 'Bakery photo'" />
            <figcaption x-show="caption" x-text="caption"></figcaption>
        </figure>
    </div>
</section>

<section class="biscotto-gallery-share" id="share-photo">
    <p>From your table</p>
    <h2>Share Your Photo</h2>
    <span>Show us how you're enjoying your Bakery on Biscotto order.</span>
    <a href="{{ route('contact.show') }}">Send Us a Message <span aria-hidden="true">→</span></a>
</section>
