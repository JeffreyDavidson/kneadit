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
<x-storefront.section bg="light" padding="xl" maxWidth="6xl">
    <x-storefront.section-header
        eyebrow="From the Kitchen"
        :title="$title"
        :subtitle="$subtitle"
    />

    <div class="grid md:grid-cols-3 gap-8">
        {{-- Lead post: spans 2 columns --}}
        @php
            $lead = $latestPosts->first();
            $lead->url = route('storefront.blog.show', $lead->slug);
            if ($lead->featured_image) {
                $lead->image_url = Storage::disk('public')->url($lead->featured_image);
            }
        @endphp
        <div class="md:col-span-2">
            <x-storefront.blog-card :post="$lead" variant="featured" />
        </div>

        {{-- Sidebar posts --}}
        @if($latestPosts->count() > 1)
        <div class="flex flex-col gap-6">
            @foreach($latestPosts->skip(1) as $blogPost)
                @php
                    $blogPost->url = route('storefront.blog.show', $blogPost->slug);
                    if ($blogPost->featured_image) {
                        $blogPost->image_url = Storage::disk('public')->url($blogPost->featured_image);
                    }
                @endphp
                <x-storefront.blog-card :post="$blogPost" variant="compact" />
            @endforeach
        </div>
        @endif
    </div>

    <div class="text-center mt-12">
        <x-storefront.button href="{{ route('storefront.blog') }}" variant="ghost" icon="arrow" class="group">
            View All Posts
        </x-storefront.button>
    </div>
</x-storefront.section>
@endif
