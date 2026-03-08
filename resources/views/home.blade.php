@extends('layouts.storefront')

@section('content')
@php
    $homepageSections = json_decode(\App\Models\Setting::get('homepage_sections', '{}'), true);
    $sections = collect($homepageSections)->filter(fn($s) => $s['visible'] ?? true)->sortBy('order');
@endphp

@foreach($sections as $key => $config)
    @switch($key)
        @case('hero')
            @include('partials.home.hero')
            @break
        @case('about')
            @include('partials.home.about')
            @break
        @case('featured_products')
            @include('partials.home.featured-products', ['config' => $config])
            @break
        @case('categories')
            @include('partials.home.categories', ['config' => $config])
            @break
        @case('reviews')
            @include('partials.home.reviews', ['config' => $config])
            @break
        @case('gallery')
            @include('partials.home.gallery', ['config' => $config])
            @break
        @case('blog')
            @include('partials.home.blog', ['config' => $config])
            @break
        @case('cta')
            @include('partials.home.cta', ['config' => $config])
            @break
        @case('social')
            @include('partials.home.social', ['config' => $config])
            @break
    @endswitch
@endforeach

{{-- Fallback: if no homepage_sections configured, show all sections with defaults --}}
@if(empty($homepageSections))
    @include('partials.home.hero')
    @include('partials.home.about')
    @include('partials.home.featured-products', ['config' => ['count' => 6, 'title' => 'Our Favorites', 'subtitle' => 'Freshly made']])
    @include('partials.home.categories', ['config' => ['title' => 'What We Bake', 'subtitle' => 'Something for everyone']])
    @include('partials.home.reviews', ['config' => ['count' => 3, 'title' => 'Kind Words', 'subtitle' => 'What our customers say']])
    @include('partials.home.gallery', ['config' => ['count' => 4, 'title' => 'Customer Gallery', 'subtitle' => 'Shared by our community']])
    @include('partials.home.blog', ['config' => ['count' => 3, 'title' => 'Latest Updates', 'subtitle' => 'From our kitchen']])
    @include('partials.home.cta', ['config' => ['heading' => 'Treat Yourself Today', 'button_text' => 'Start Your Order']])
    @include('partials.home.social', ['config' => []])
@endif
@endsection
