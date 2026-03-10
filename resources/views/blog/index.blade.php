@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp

{{-- Dark Hero Banner --}}
<section class="relative py-24 overflow-hidden" style="background: var(--warm-900);">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at center, rgba(212,146,12,0.06), transparent 70%);"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-6">
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">From the Kitchen</span>
            <span class="block w-8 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
        </div>
        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4" style="color: var(--warm-100);">Blog & Updates</h1>
        <p class="text-lg" style="color: var(--warm-400);">Stories, recipes, and news from {{ $storeName }}</p>
    </div>
</section>

<section class="py-16 px-4" style="background: var(--warm-100);">
    <div class="max-w-6xl mx-auto">

    @if($posts->isEmpty())
        {{-- Premium Empty State --}}
        <div class="max-w-2xl mx-auto text-center py-16">
            {{-- Decorative icon --}}
            <div class="w-20 h-20 rounded-full mx-auto mb-8 flex items-center justify-center" style="background: var(--warm-200);">
                <svg class="w-10 h-10" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                </svg>
            </div>

            <h2 class="font-display text-3xl font-bold mb-4" style="color: var(--warm-900);">We're Cooking Up Something Good</h2>
            <p class="text-lg leading-relaxed mb-6" style="color: var(--warm-600);">
                Our blog is warming up! Soon you'll find recipes, baking tips, behind-the-scenes stories, and all the latest news from our kitchen.
            </p>
            <p class="font-script text-xl mb-10" style="color: var(--warm-500);">Stay tuned — good things take time.</p>

            {{-- Faux preview cards --}}
            <div class="grid md:grid-cols-3 gap-4 opacity-30">
                @for($i = 0; $i < 3; $i++)
                <div class="rounded-2xl overflow-hidden" style="background: white; border: 1px solid var(--warm-200);">
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--warm-200), var(--warm-300));"></div>
                    <div class="p-5 space-y-3">
                        <div class="h-3 rounded-full" style="background: var(--warm-200); width: {{ [85, 70, 90][$i] }}%;"></div>
                        <div class="h-2 rounded-full" style="background: var(--warm-200); width: {{ [60, 80, 55][$i] }}%;"></div>
                        <div class="h-2 rounded-full" style="background: var(--warm-200); width: {{ [45, 50, 65][$i] }}%;"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    @else
        {{-- Blog Grid --}}
        @php $featured = $posts->first(); @endphp

        {{-- Featured Post --}}
        <a href="{{ route('storefront.blog.show', $featured->slug) }}" class="group block rounded-2xl overflow-hidden mb-10 transition-all duration-300 hover:shadow-2xl" style="background: white;">
            <div class="grid md:grid-cols-2 gap-0">
                <div class="relative overflow-hidden" style="min-height: 300px;">
                    @if($featured->featured_image)
                        <img src="{{ Storage::disk('public')->url($featured->featured_image) }}" alt="{{ $featured->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700));">
                            <svg class="w-16 h-16" style="color: var(--warm-500); opacity: 0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col justify-center p-8 md:p-12">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 self-start" style="background: var(--warm-500); color: var(--warm-900);">Latest</span>
                    <p class="text-xs font-semibold uppercase tracking-widest mb-3" style="color: var(--warm-500);">{{ $featured->published_at->format('M j, Y') }}@if($featured->author_name) · {{ $featured->author_name }}@endif</p>
                    <h2 class="font-display text-2xl md:text-3xl font-bold mb-3 group-hover:underline" style="color: var(--warm-900);">{{ $featured->title }}</h2>
                    @if($featured->excerpt)
                    <p class="text-base leading-relaxed" style="color: var(--warm-600);">{{ Str::limit($featured->excerpt, 200) }}</p>
                    @endif
                    @if($featured->tags)
                    <div class="flex flex-wrap gap-2 mt-4">
                        @foreach($featured->tags as $tag)
                        <span class="text-xs px-3 py-1 rounded-full" style="background: var(--warm-200); color: var(--warm-600);">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </a>

        {{-- Remaining posts grid --}}
        @if($posts->count() > 1)
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts->skip(1) as $post)
            <a href="{{ route('storefront.blog.show', $post->slug) }}" class="group block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: white;">
                <div class="relative overflow-hidden" style="aspect-ratio: 16/9;">
                    @if($post->featured_image)
                        <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-200), var(--warm-300));">
                            <svg class="w-10 h-10" style="color: var(--warm-400); opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: var(--warm-500);">{{ $post->published_at->format('M j, Y') }}@if($post->author_name) · {{ $post->author_name }}@endif</p>
                    <h2 class="font-display text-xl font-bold mb-2 group-hover:underline" style="color: var(--warm-900);">{{ $post->title }}</h2>
                    @if($post->excerpt)
                    <p class="text-sm line-clamp-2" style="color: var(--warm-600);">{{ $post->excerpt }}</p>
                    @endif
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
        @endif

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
    </div>
</section>
@endsection
