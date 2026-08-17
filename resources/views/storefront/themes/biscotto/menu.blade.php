<section class="biscotto-menu-hero">
    <p class="biscotto-menu-eyebrow">{{ $heroEyebrow }}</p>
    <h1>{{ $content['hero_title'] ?? 'Our Menu' }}</h1>
    <p>{{ $content['hero_subtitle'] ?? 'Fresh from our oven, made with love and flour dust.' }}</p>
</section>

<div class="biscotto-menu-scene">
    <div class="biscotto-parchment-wrap">
        <main class="biscotto-parchment">
            <header class="biscotto-menu-heading">
                <p>Bakery on Biscotto</p>
                <h2>Our Menu</h2>
                <div class="biscotto-menu-flourish" aria-hidden="true"><span></span><span>✦</span><span></span></div>
                <blockquote>“Good bread is the most fundamentally satisfying of all foods.”</blockquote>
            </header>
            @if (count($categories) > 1)
                <nav class="biscotto-menu-tabs" aria-label="Menu categories">
                    @foreach ($categories as $category)
                        <a href="#category-{{ $category->id }}">{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif
            @forelse ($categories as $category)
                <section id="category-{{ $category->id }}" class="biscotto-menu-category">
                    <header>
                        <span>✦</span>
                        <h3>{{ $category->name }}</h3>
                        <span>✦</span>
                    </header>
                    @if ($category->description)
                        <p class="biscotto-category-description">{{ $category->description }}</p>
                    @endif
                    @foreach ($category->products as $product)
                        <article class="biscotto-menu-item">
                            <div class="biscotto-menu-item-row">
                                <h4>{{ $product->name }}</h4>
                                <span class="biscotto-menu-dots" aria-hidden="true"></span>
                                <strong>@money($product->price)</strong>
                            </div>
                            @if ($product->description)
                                <p>{{ $product->description }}</p>
                            @endif
                        </article>
                    @endforeach
                </section>
            @empty
                <p class="biscotto-menu-empty">
                    {{ $content['empty_message'] ?? 'Our menu is being updated. Check back soon.' }}
                </p>
            @endforelse
            <footer class="biscotto-menu-order">
                <p>{{ $content['cta_script'] ?? 'Ready to order?' }}</p>
                <h3>{{ $content['cta_heading'] ?? 'Let\'s get baking.' }}</h3>
                <span>{{ $ctaDesc }}</span>
                <a href="{{ route('order.create') }}">{{ $content['cta_button'] ?? 'Place an Order' }} <span aria-hidden="true">→</span></a>
            </footer>
        </main>
    </div>
</div>
