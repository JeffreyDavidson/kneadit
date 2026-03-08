@php
    $count = $config['count'] ?? 3;
    $title = $config['title'] ?? 'Latest Updates';
    $subtitle = $config['subtitle'] ?? 'From our kitchen';
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
<section class="py-20 px-4" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto">
        <div class="section-divider mb-14"></div>
        <h2 class="font-display text-3xl md:text-5xl font-semibold mb-12" style="color: var(--warm-900);">{{ $title }}</h2>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Lead post: spans 2 columns --}}
            @php $lead = $latestPosts->first(); @endphp
            <a href="{{ route('storefront.blog.show', $lead->slug) }}" class="md:col-span-2 group rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-xl" style="background: white; border: 1px solid var(--warm-200);">
                @if($lead->featured_image)
                <div class="overflow-hidden" style="aspect-ratio: 16/9;">
                    <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                </div>
                @else
                <div class="flex items-center justify-center" style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                    <svg class="w-12 h-12" style="color: var(--warm-500); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                @endif
                <div class="p-8">
                    <p class="text-xs font-medium mb-3 uppercase tracking-widest" style="color: var(--warm-500);">{{ $lead->published_at->format('M j, Y') }}</p>
                    <h3 class="font-display text-2xl md:text-3xl font-semibold mb-3 group-hover:underline" style="color: var(--warm-900);">{{ $lead->title }}</h3>
                    @if($lead->excerpt)
                    <p class="text-base leading-relaxed" style="color: var(--warm-600);">{{ $lead->excerpt }}</p>
                    @endif
                </div>
            </a>

            {{-- Sidebar posts --}}
            @if($latestPosts->count() > 1)
            <div class="flex flex-col gap-8">
                @foreach($latestPosts->skip(1) as $blogPost)
                <a href="{{ route('storefront.blog.show', $blogPost->slug) }}" class="group rounded-2xl overflow-hidden flex-1 transition-all duration-300 hover:shadow-xl" style="background: white; border: 1px solid var(--warm-200);">
                    @if($blogPost->featured_image)
                    <div class="overflow-hidden" style="aspect-ratio: 16/9;">
                        <img src="{{ Storage::disk('public')->url($blogPost->featured_image) }}" alt="{{ $blogPost->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    </div>
                    @endif
                    <div class="p-5">
                        <p class="text-xs font-medium mb-2" style="color: var(--warm-500);">{{ $blogPost->published_at->format('M j, Y') }}</p>
                        <h3 class="font-display text-lg font-semibold group-hover:underline" style="color: var(--warm-900);">{{ $blogPost->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('storefront.blog') }}" class="inline-flex items-center gap-2 font-display font-medium transition-colors hover:underline" style="color: var(--warm-700);">
                View All Posts
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif
