@if ($latestPosts->isNotEmpty())
<section class="py-24 px-4 bg-warm-100">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-14">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="block w-8 h-px bg-warm-500"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold text-warm-500">Blog</span>
                </div>
                <h2 class="font-display text-3xl md:text-5xl font-bold text-warm-900">{{ $title }}</h2>
                <p class="mt-2 text-base text-warm-600">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('storefront.blog') }}" class="hidden md:inline-flex items-center gap-2 mt-4 md:mt-0 font-semibold transition-all duration-200 hover:gap-3 text-warm-600">
                View All Posts
                <x-heroicon-o-arrow-right class="w-4 h-4" stroke-width="2" />
            </a>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Lead post: spans 2 columns --}}
            @php $lead = $latestPosts->first(); @endphp
            <a href="{{ route('storefront.blog.show', $lead->slug) }}" class="md:col-span-2 group rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl bg-white">
                <div class="relative overflow-hidden" style="aspect-ratio: 16/9;">
                    @if ($lead->featured_image)
                        <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                            <x-heroicon-o-document-text class="w-16 h-16 text-warm-500/30" />
                        </div>
                    @endif
                </div>
                <div class="p-8">
                    <p class="text-xs font-semibold uppercase tracking-widest mb-3 text-warm-500">{{ $lead->published_at->format('M j, Y') }}</p>
                    <h3 class="font-display text-2xl md:text-3xl font-bold mb-3 transition-colors group-hover:underline text-warm-900">{{ $lead->title }}</h3>
                    @if ($lead->excerpt)
                    <p class="text-base leading-relaxed text-warm-600">{{ Str::limit($lead->excerpt, 160) }}</p>
                    @endif
                </div>
            </a>

            {{-- Sidebar posts --}}
            @if ($latestPosts->count() > 1)
            <div class="flex flex-col gap-6">
                @foreach ($latestPosts->skip(1) as $post)
                <a href="{{ route('storefront.blog.show', $post->slug) }}" class="group rounded-2xl overflow-hidden flex-1 transition-all duration-300 hover:shadow-xl bg-white">
                    @if ($post->featured_image)
                    <div class="overflow-hidden" style="aspect-ratio: 16/9;">
                        <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    </div>
                    @endif
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-2 text-warm-500">{{ $post->published_at->format('M j, Y') }}</p>
                        <h3 class="font-display text-lg font-bold transition-colors group-hover:underline text-warm-900">{{ $post->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Mobile CTA --}}
        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-2 font-semibold text-warm-600">
                View All Posts
                <x-heroicon-o-arrow-right class="w-4 h-4" stroke-width="2" />
            </a>
        </div>
    </div>
</section>
@endif
