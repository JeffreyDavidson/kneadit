@extends('layouts.storefront')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-1 text-sm mb-8 hover:underline" style="color: var(--warm-500);">
        ← Back to Blog
    </a>

    <article>
        @if($post->featured_image)
            <div class="rounded-2xl overflow-hidden mb-8">
                <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover">
            </div>
        @endif

        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4" style="color: var(--warm-900);">{{ $post->title }}</h1>

        <div class="flex flex-wrap items-center gap-3 mb-6 text-sm" style="color: var(--warm-500);">
            @if($post->author_name)
                <span>By {{ $post->author_name }}</span>
                <span>·</span>
            @endif
            <span>{{ $post->published_at?->format('F j, Y') }}</span>
        </div>

        @if($post->tags)
            <div class="flex flex-wrap gap-2 mb-8">
                @foreach($post->tags as $tag)
                    <span class="text-xs px-3 py-1 rounded-full" style="background: var(--warm-100); color: var(--warm-600);">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        <div class="prose prose-lg max-w-none mb-12" style="color: var(--warm-800);">
            {!! $post->body !!}
        </div>

        <!-- Share -->
        <div class="border-t pt-6 mb-12" style="border-color: var(--warm-200);">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium" style="color: var(--warm-600);">Share this post:</span>
                <button
                    x-data
                    @click="navigator.clipboard.writeText(window.location.href); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy Link', 2000)"
                    class="text-sm px-4 py-1.5 rounded-full transition-colors cursor-pointer"
                    style="background: var(--warm-100); color: var(--warm-700);"
                >Copy Link</button>
            </div>
        </div>
    </article>

    <!-- Related Posts -->
    @if($related->isNotEmpty())
        <div class="border-t pt-10" style="border-color: var(--warm-200);">
            <h2 class="font-display text-2xl font-bold mb-6" style="color: var(--warm-900);">More Posts</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($related as $relatedPost)
                    <a href="{{ route('storefront.blog.show', $relatedPost->slug) }}" class="group block rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" style="background: var(--warm-50); border: 1px solid var(--warm-200);">
                        @if($relatedPost->featured_image)
                            <div class="aspect-video overflow-hidden">
                                <img src="{{ Storage::disk('public')->url($relatedPost->featured_image) }}" alt="{{ $relatedPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="aspect-video flex items-center justify-center" style="background: var(--warm-100);">
                                <svg class="w-8 h-8" style="color: var(--warm-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                        @endif
                        <div class="p-4">
                            <h3 class="font-display font-semibold group-hover:underline" style="color: var(--warm-900);">{{ $relatedPost->title }}</h3>
                            <p class="text-xs mt-1" style="color: var(--warm-500);">{{ $relatedPost->published_at?->format('M j, Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
