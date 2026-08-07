<x-layouts.storefront>
    <section class="bg-warm-900 relative overflow-hidden py-16">
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center">
            <h1 class="font-display text-warm-100 mb-4 text-4xl font-bold md:text-5xl">Our Blog</h1>
            <p class="text-warm-400 text-lg">Stories, recipes, and updates from our kitchen to yours.</p>
        </div>
    </section>

    <section class="bg-warm-100">
        <div class="mx-auto max-w-5xl px-4 py-16">
            @if ($posts->count() > 0)
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <a
                            href="{{ route('storefront.blog.show', $post) }}"
                            class="border-warm-200 block overflow-hidden rounded-2xl border bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                        >
                            @if ($post->featured_image)
                                <img
                                    src="{{ Storage::url($post->featured_image) }}"
                                    alt="{{ $post->title }}"
                                    class="h-48 w-full object-cover"
                                />
                            @else
                                <div class="bg-warm-200 flex h-48 w-full items-center justify-center">
                                    <span class="text-3xl">🍞</span>
                                </div>
                            @endif
                            <div class="p-6">
                                @if ($post->tags)
                                    <div class="mb-3 flex flex-wrap gap-2">
                                        @foreach ($post->tags as $tag)
                                            <x-storefront.pill tone="subtle" size="xs">{{ $tag }}</x-storefront.pill>
                                        @endforeach
                                    </div>
                                @endif
                                <h2 class="font-display text-warm-800 mb-2 text-lg font-semibold">
                                    {{ $post->title }}
                                </h2>
                                @if ($post->excerpt)
                                    <p class="text-warm-600 mb-3 text-sm">{{ Str::limit($post->excerpt, 120) }}</p>
                                @endif
                                <div class="text-warm-500 flex items-center justify-between text-xs">
                                    @if ($post->author_name)
                                        <span>{{ $post->author_name }}</span>
                                    @endif
                                    <span>{{ $post->published_at?->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">{{ $posts->links() }}</div>
            @else
                <div class="py-16 text-center">
                    <p class="mb-2 text-2xl">📝</p>
                    <h2 class="font-display text-warm-800 mb-2 text-2xl font-bold">No posts yet</h2>
                    <p class="text-warm-600">Check back soon for stories and updates!</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
