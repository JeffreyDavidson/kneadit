@php
    $count = $config['count'] ?? 3;
    $title = $config['title'] ?? 'From the Kitchen';
    $subtitle = $config['subtitle'] ?? 'Stories, tips, and updates';
    try {
        $latestPosts = \App\Models\BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take($count)
            ->get();
    } catch (\Exception $e) {
        $latestPosts = collect();
    }
@endphp
@if($latestPosts->isNotEmpty())
<section class="py-24 px-4" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-14">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
                    <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">Blog</span>
                </div>
                <h2 class="font-display text-3xl md:text-5xl font-bold" style="color: var(--warm-900);">{{ $title }}</h2>
                <p class="mt-2 text-base" style="color: var(--warm-600);">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('storefront.blog') }}" class="hidden md:inline-flex items-center gap-2 mt-4 md:mt-0 font-semibold transition-all duration-200 hover:gap-3" style="color: var(--warm-600);">
                View All Posts
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Lead post: spans 2 columns --}}
            @php $lead = $latestPosts->first(); @endphp
            <a href="{{ route('storefront.blog.show', $lead->slug) }}" class="md:col-span-2 group rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl" style="background: white;">
                <div class="relative overflow-hidden" style="aspect-ratio: 16/9;">
                    @if($lead->featured_image)
                        <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                            <svg class="w-16 h-16" style="color: var(--warm-500); opacity: 0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="p-8">
                    <p class="text-xs font-semibold uppercase tracking-widest mb-3" style="color: var(--warm-500);">{{ $lead->published_at->format('M j, Y') }}</p>
                    <h3 class="font-display text-2xl md:text-3xl font-bold mb-3 transition-colors group-hover:underline" style="color: var(--warm-900);">{{ $lead->title }}</h3>
                    @if($lead->excerpt)
                    <p class="text-base leading-relaxed" style="color: var(--warm-600);">{{ Str::limit($lead->excerpt, 160) }}</p>
                    @endif
                </div>
            </a>

            {{-- Sidebar posts --}}
            @if($latestPosts->count() > 1)
            <div class="flex flex-col gap-6">
                @foreach($latestPosts->skip(1) as $post)
                <a href="{{ route('storefront.blog.show', $post->slug) }}" class="group rounded-2xl overflow-hidden flex-1 transition-all duration-300 hover:shadow-xl" style="background: white;">
                    @if($post->featured_image)
                    <div class="overflow-hidden" style="aspect-ratio: 16/9;">
                        <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    </div>
                    @endif
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: var(--warm-500);">{{ $post->published_at->format('M j, Y') }}</p>
                        <h3 class="font-display text-lg font-bold transition-colors group-hover:underline" style="color: var(--warm-900);">{{ $post->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Mobile CTA --}}
        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-2 font-semibold" style="color: var(--warm-600);">
                View All Posts
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
