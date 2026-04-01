<x-layouts.storefront>

<section class="relative overflow-hidden py-16 bg-warm-900">
    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <h1 class="font-display text-4xl md:text-5xl font-bold mb-4 text-warm-100">Our Blog</h1>
        <p class="text-lg text-warm-400">Stories, recipes, and updates from our kitchen to yours.</p>
    </div>
</section>

<section class="bg-warm-100">
    <div class="max-w-5xl mx-auto px-4 py-16">
        @if ($posts->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($posts as $post)
                    <a href="{{ route('storefront.blog.show', $post) }}" class="block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border border-warm-200">
                        @if ($post->featured_image)
                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 flex items-center justify-center bg-warm-200">
                                <span class="text-3xl">🍞</span>
                            </div>
                        @endif
                        <div class="p-6">
                            @if ($post->tags)
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach ($post->tags as $tag)
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full" style="background: rgba(212,146,12,0.15); color: var(--warm-500);">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <h2 class="font-display text-lg font-semibold mb-2 text-warm-800">{{ $post->title }}</h2>
                            @if ($post->excerpt)
                                <p class="text-sm mb-3 text-warm-600">{{ Str::limit($post->excerpt, 120) }}</p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-warm-500">
                                @if ($post->author_name)
                                    <span>{{ $post->author_name }}</span>
                                @endif
                                <span>{{ $post->published_at?->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <p class="text-2xl mb-2">📝</p>
                <h2 class="font-display text-2xl font-bold mb-2 text-warm-800">No posts yet</h2>
                <p class="text-warm-600">Check back soon for stories and updates!</p>
            </div>
        @endif
    </div>
</section>

</x-layouts.storefront>
