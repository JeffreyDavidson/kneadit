@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp

<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="font-display text-4xl font-bold mb-4" style="color: var(--warm-900);">Blog & Updates</h1>
        <p class="text-lg" style="color: var(--warm-600);">Stories, recipes, and news from {{ $storeName }}</p>
        <a href="{{ route('storefront.blog.feed') }}" class="inline-flex items-center gap-1 mt-3 text-sm" style="color: var(--warm-500);">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a1 1 0 000 2c5.523 0 10 4.477 10 10a1 1 0 102 0C17 8.373 11.627 3 5 3zm0 4a1 1 0 000 2 6 6 0 016 6 1 1 0 102 0 8 8 0 00-8-8zm0 4a1 1 0 000 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4zm0 4a1 1 0 100 2 1 1 0 000-2z"/></svg>
            RSS Feed
        </a>
    </div>

    @if($posts->isEmpty())
        <div class="text-center py-16">
            <p class="text-lg" style="color: var(--warm-500);">No posts yet. Check back soon!</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <a href="{{ route('storefront.blog.show', $post->slug) }}" class="group block rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" style="background: var(--warm-50); border: 1px solid var(--warm-200);">
                    @if($post->featured_image)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="aspect-video flex items-center justify-center" style="background: var(--warm-100);">
                            <svg class="w-12 h-12" style="color: var(--warm-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h2 class="font-display text-xl font-semibold mb-2 group-hover:underline" style="color: var(--warm-900);">{{ $post->title }}</h2>
                        @if($post->excerpt)
                            <p class="text-sm mb-3 line-clamp-3" style="color: var(--warm-600);">{{ $post->excerpt }}</p>
                        @endif
                        <div class="flex items-center gap-3 text-xs" style="color: var(--warm-500);">
                            <span>{{ $post->published_at->format('M j, Y') }}</span>
                            @if($post->author_name)
                                <span>·</span>
                                <span>{{ $post->author_name }}</span>
                            @endif
                        </div>
                        @if($post->tags)
                            <div class="flex flex-wrap gap-1 mt-3">
                                @foreach($post->tags as $tag)
                                    <span class="text-xs px-2 py-0.5 rounded-full" style="background: var(--warm-100); color: var(--warm-600);">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
