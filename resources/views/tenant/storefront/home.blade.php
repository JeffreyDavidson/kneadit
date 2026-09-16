<x-layouts.storefront>
    @foreach ($sections as $key => $config)
        @switch ($key)
            @case ('hero')
                <x-storefront.home.hero />
                @break
            @case ('about')
                <x-storefront.home.about />
                @break
            @case ('featured_products')
                <x-storefront.home.featured-products :config="$config" />
                @break
            @case ('categories')
                <x-storefront.home.categories :config="$config" />
                @break
            @case ('reviews')
                <x-storefront.home.reviews :config="$config" />
                @break
            @case ('gallery')
                <x-storefront.home.gallery :config="$config" />
                @break
            @case ('blog')
                <x-storefront.home.blog-posts :config="$config" />
                @break
            @case ('cta')
                <x-storefront.home.cta :config="$config" />
                @break
            @case ('social')
                <x-storefront.home.social />
                @break
        @endswitch
    @endforeach

    {{-- Fallback: if no homepage_sections configured, show all sections with defaults --}}
    @if (empty($settings->homepage->sections))
        <x-storefront.home.hero />
        <x-storefront.home.about />
        <x-storefront.home.featured-products :config="['count' => 6, 'title' => 'Our Favorites', 'subtitle' => 'Freshly made']" />
        <x-storefront.home.categories :config="['title' => 'What We Bake', 'subtitle' => 'Something for everyone']" />
        <x-storefront.home.reviews :config="['count' => 3, 'title' => 'Kind Words', 'subtitle' => 'What our customers say']" />
        <x-storefront.home.gallery :config="['count' => 4, 'title' => 'Customer Gallery', 'subtitle' => 'Shared by our community']" />
        <x-storefront.home.blog-posts :config="['count' => 3, 'title' => 'Latest Updates', 'subtitle' => 'From our kitchen']" />
        <x-storefront.home.cta :config="['heading' => 'Treat Yourself Today', 'button_text' => 'Start Your Order']" />
        <x-storefront.home.social />
    @endif
</x-layouts.storefront>
