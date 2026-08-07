@if ($latestPosts->isNotEmpty())
    <section class="bg-warm-100 px-4 py-24">
        <div class="mx-auto max-w-6xl">
            {{-- Header --}}
            <div class="mb-14 flex flex-col md:flex-row md:items-end md:justify-between">
                <div>
                    <x-storefront.eyebrow align="left" class="mb-4">Blog</x-storefront.eyebrow>
                    <h2 class="font-display text-warm-900 text-3xl font-bold md:text-5xl">{{ $title }}</h2>
                    <p class="text-warm-600 mt-2 text-base">{{ $subtitle }}</p>
                </div>
                <a
                    href="{{ route('storefront.blog') }}"
                    class="text-warm-600 mt-4 hidden items-center gap-2 font-semibold transition-all duration-200 hover:gap-3 md:mt-0 md:inline-flex"
                >
                    View All Posts
                    <x-heroicon-o-arrow-right class="h-4 w-4" stroke-width="2" />
                </a>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                {{-- Lead post: spans 2 columns --}}
                @php $lead = $latestPosts->first(); @endphp
                <a
                    href="{{ route('storefront.blog.show', $lead->slug) }}"
                    class="group overflow-hidden rounded-2xl bg-white transition-all duration-300 hover:shadow-2xl md:col-span-2"
                >
                    <div class="relative overflow-hidden" style="aspect-ratio: 16/9">
                        @if ($lead->featured_image)
                            <img
                                src="{{ Storage::disk('public')->url($lead->featured_image) }}"
                                alt="{{ $lead->title }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                        @else
                            <div
                                class="flex h-full w-full items-center justify-center"
                                style="background: linear-gradient(135deg, var(--warm-800), var(--warm-700))"
                            >
                                <x-heroicon-o-document-text class="text-warm-500/30 h-16 w-16" />
                            </div>
                        @endif
                    </div>
                    <div class="p-8">
                        <p class="text-warm-500 mb-3 text-xs font-semibold tracking-widest uppercase">
                            {{ $lead->published_at->format('M j, Y') }}
                        </p>
                        <h3 class="font-display text-warm-900 mb-3 text-2xl font-bold transition-colors group-hover:underline md:text-3xl">
                            {{ $lead->title }}
                        </h3>
                        @if ($lead->excerpt)
                            <p class="text-warm-600 text-base leading-relaxed">{{ Str::limit($lead->excerpt, 160) }}</p>
                        @endif
                    </div>
                </a>

                {{-- Sidebar posts --}}
                @if ($latestPosts->count() > 1)
                    <div class="flex flex-col gap-6">
                        @foreach ($latestPosts->skip(1) as $post)
                            <a
                                href="{{ route('storefront.blog.show', $post->slug) }}"
                                class="group flex-1 overflow-hidden rounded-2xl bg-white transition-all duration-300 hover:shadow-xl"
                            >
                                @if ($post->featured_image)
                                    <div class="overflow-hidden" style="aspect-ratio: 16/9">
                                        <img
                                            src="{{ Storage::disk('public')->url($post->featured_image) }}"
                                            alt="{{ $post->title }}"
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        />
                                    </div>
                                @endif
                                <div class="p-5">
                                    <p class="text-warm-500 mb-2 text-xs font-semibold tracking-widest uppercase">
                                        {{ $post->published_at->format('M j, Y') }}
                                    </p>
                                    <h3 class="font-display text-warm-900 text-lg font-bold transition-colors group-hover:underline">
                                        {{ $post->title }}
                                    </h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Mobile CTA --}}
            <div class="mt-10 text-center md:hidden">
                <a
                    href="{{ route('storefront.blog') }}"
                    class="text-warm-600 inline-flex items-center gap-2 font-semibold"
                >
                    View All Posts
                    <x-heroicon-o-arrow-right class="h-4 w-4" stroke-width="2" />
                </a>
            </div>
        </div>
    </section>
@endif
