<x-layouts.storefront>

<article class="bg-warm-100">
    <div class="max-w-3xl mx-auto px-4 py-16">
        {{-- Back link --}}
        <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-2 text-sm font-medium mb-8 transition-colors text-warm-500">
            <x-heroicon-o-arrow-left class="w-4 h-4" stroke-width="2" />
            Back to Blog
        </a>

        {{-- Tags --}}
        @if ($post->tags)
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($post->tags as $tag)
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-warm-500/15 text-warm-500">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Title --}}
        <h1 class="font-display text-3xl md:text-4xl lg:text-5xl font-bold leading-tight mb-4 text-warm-800">{{ $post->title }}</h1>

        {{-- Meta --}}
        <div class="flex items-center gap-4 text-sm mb-8 text-warm-500">
            @if ($post->author_name)
                <span>By {{ $post->author_name }}</span>
                <span>&middot;</span>
            @endif
            <span>{{ $post->published_at?->format('F j, Y') }}</span>
        </div>

        {{-- Featured image --}}
        @if ($post->featured_image)
            <div class="rounded-2xl overflow-hidden mb-10">
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full">
            </div>
        @endif

        {{-- Excerpt --}}
        @if ($post->excerpt)
            <p class="text-lg leading-relaxed mb-8 font-medium text-warm-700">{{ $post->excerpt }}</p>
        @endif

        {{-- Body --}}
        <div class="prose prose-lg max-w-none text-warm-700">
            {!! clean($post->body) !!}
        </div>
    </div>
</article>

</x-layouts.storefront>
