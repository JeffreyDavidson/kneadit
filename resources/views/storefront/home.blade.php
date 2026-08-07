<x-layouts.storefront>
    @foreach ($sections as $key => $config)
        @switch ($key)
            @case ('hero')
                <x-home.hero />
                @break
            @case ('about')
                @include('partials.home.about')
                @break
            @case ('featured_products')
                <x-home.featured-products :config="$config" />
                @break
            @case ('categories')
                <x-home.categories :config="$config" />
                @break
            @case ('reviews')
                <x-home.reviews :config="$config" />
                @break
            @case ('gallery')
                <x-home.gallery :config="$config" />
                @break
            @case ('blog')
                <x-home.blog-posts :config="$config" />
                @break
            @case ('cta')
                @include('partials.home.cta', ['config' => $config])
                @break
            @case ('social')
                @include('partials.home.social', ['config' => $config])
                @break
        @endswitch
    @endforeach

    {{-- Fallback: if no homepage_sections configured, show all sections with defaults --}}
    @if (empty($settings->homepage->sections))
        <x-home.hero />
        @include('partials.home.about')
        <x-home.featured-products :config="['count' => 6, 'title' => 'Our Favorites', 'subtitle' => 'Freshly made']" />
        <x-home.categories :config="['title' => 'What We Bake', 'subtitle' => 'Something for everyone']" />
        <x-home.reviews :config="['count' => 3, 'title' => 'Kind Words', 'subtitle' => 'What our customers say']" />
        <x-home.gallery :config="['count' => 4, 'title' => 'Customer Gallery', 'subtitle' => 'Shared by our community']" />
        <x-home.blog-posts :config="['count' => 3, 'title' => 'Latest Updates', 'subtitle' => 'From our kitchen']" />
        @include('partials.home.cta', ['config' => ['heading' => 'Treat Yourself Today', 'button_text' => 'Start Your Order']])
        @include('partials.home.social', ['config' => []])
    @endif
</x-layouts.storefront>
