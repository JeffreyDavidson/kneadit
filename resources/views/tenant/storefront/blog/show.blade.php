@php
    // Body comes in as plain text with \n\n paragraph breaks. Split, escape,
    // then rehydrate the limited inline markdown we use (**bold**) so the
    // article reads like a real article instead of a wall of text.
    $rawParagraphs = preg_split("/\n\n+/", trim((string) $post->body));
    $paragraphs = collect($rawParagraphs)
        ->map(fn (string $p): string => preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', e(trim($p))));

    $readingTime = max(1, (int) ceil(str_word_count((string) $post->body) / 200));
    $publishedDate = $post->published_at?->format('F j, Y') ?? '';
    $authorName = $post->author_name ?: ($settings->store->name ?? 'The Bakery');
    $tagList = is_array($post->tags) ? $post->tags : [];
@endphp

<x-layouts.storefront>
    <x-slot:styles>
        <style @cspnonce>
            /* Drop cap on the first paragraph of the article body. The Pophams
           letterpress move applied to long-form text — a single oversized
           display-font initial that anchors the reader's eye and signals "this
           is meant to be read." */
            .article-body > p:first-of-type::first-letter {
                float: left;
                font-family: var(--font-display);
                font-size: 4.75em;
                line-height: 0.86;
                margin: 0.05em 0.16em -0.04em 0;
                color: var(--warm-700);
                font-weight: 500;
            }

            .article-body p {
                margin-bottom: 1.4em;
            }

            .article-body p:last-child {
                margin-bottom: 0;
            }

            .article-body strong {
                color: var(--warm-700);
                font-weight: 600;
            }

            /* The empty-state placeholder for posts without a featured image.
           Same brand language as the product placeholder (cream surface,
           inset hairline frame, asterisk-divider flourish) but proportioned
           for an article header. */
            .article-figure-placeholder {
                position: relative;
                width: 100%;
                aspect-ratio: 16 / 7;
                background: var(--warm-100);
                border: 1px solid var(--warm-300);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 1.5rem;
            }

            .article-figure-placeholder::before,
            .article-figure-placeholder::after {
                content: '';
                position: absolute;
                left: 1.25rem;
                right: 1.25rem;
                height: 1px;
                background: var(--warm-500);
                opacity: 0.4;
            }

            .article-figure-placeholder::before {
                top: 1.25rem;
            }
            .article-figure-placeholder::after {
                bottom: 1.25rem;
            }

            @media (max-width: 768px) {
                .article-body > p:first-of-type::first-letter {
                    font-size: 3.6em;
                }
            }
        </style>
    </x-slot:styles>

    <article style="background: var(--warm-100)">
        {{-- ═══ Top utility row + article header ═══ --}}
        <header class="mx-auto max-w-3xl px-5 md:px-8" style="padding-top: clamp(2.5rem, 6vw, 5rem)">
            <a
                href="{{ route('storefront.blog') }}"
                class="mb-12 inline-flex items-center gap-3"
                style="
                    color: var(--warm-700);
                    font-size: 0.8125rem;
                    letter-spacing: 0.16em;
                    text-transform: uppercase;
                    font-weight: 500;
                "
            >
                <span
                    aria-hidden="true"
                    style="display: inline-block; width: 1.5rem; height: 1px; background: var(--warm-500)"
                ></span>
                Back to Blog
            </a>

            @if (count($tagList) > 0)
                <div class="mb-8 flex flex-wrap items-center gap-x-3 gap-y-2" style="color: var(--warm-700)">
                    <span
                        aria-hidden="true"
                        style="display: inline-block; width: 1.25rem; height: 1px; background: var(--warm-500)"
                    ></span>
                    @foreach ($tagList as $i => $tag)
                        <span
                            class="font-body font-semibold uppercase"
                            style="font-size: 0.6875rem; letter-spacing: 0.22em"
                        >{{ $tag }}</span>
                        @if (! $loop->last)
                            <span aria-hidden="true" style="opacity: 0.5">·</span>
                        @endif
                    @endforeach
                </div>
            @endif

            <h1
                class="font-display tracking-tight"
                style="
                    color: var(--warm-700);
                    font-size: clamp(2.25rem, 4.4vw, 3.75rem);
                    font-weight: 500;
                    line-height: 1.04;
                "
            >
                {{ $post->title }}
            </h1>

            {{-- Byline strip: hairlines above + below, generous spacing.
             Three groups separated by interpunct — author, date, reading
             time — sized so each reads as discrete metadata, not a sentence. --}}
            <div
                class="mt-10 mb-2 flex flex-wrap items-baseline justify-between"
                style="
                    color: var(--warm-700);
                    column-gap: 1.5rem;
                    row-gap: 0.5rem;
                    padding: 1.125rem 0;
                    border-top: 1px solid color-mix(in oklab, var(--warm-700) 20%, transparent);
                    border-bottom: 1px solid color-mix(in oklab, var(--warm-700) 20%, transparent);
                "
            >
                <div class="flex flex-wrap items-baseline" style="column-gap: 1.25rem; row-gap: 0.5rem">
                    <span class="font-body" style="font-size: 0.9375rem">
                        By <span style="font-weight: 600">{{ $authorName }}</span>
                    </span>
                    <span aria-hidden="true" style="opacity: 0.35">·</span>
                    <time
                        datetime="{{ $post->published_at?->toIso8601String() }}"
                        class="font-body"
                        style="font-size: 0.9375rem"
                    >
                        {{ $publishedDate }}
                    </time>
                </div>
                <span class="font-body uppercase" style="font-size: 0.6875rem; letter-spacing: 0.22em; opacity: 0.65">
                    {{ $readingTime }} min read
                </span>
            </div>
        </header>

        {{-- ═══ Featured image — only renders when one is uploaded.
         When no image exists, we skip this section entirely. Editorial
         discipline: if there's nothing to show, don't fill the slot with
         a designed-empty-box that just reads as "image missing." Body
         typography carries the page. --}}
        @if ($post->featured_image)
            <figure
                class="mx-auto max-w-5xl px-5 md:px-8"
                style="margin-top: clamp(2.5rem, 5vw, 4rem); margin-bottom: clamp(2.5rem, 5vw, 4rem)"
            >
                <img
                    src="{{ Storage::url($post->featured_image) }}"
                    alt="{{ $post->title }}"
                    class="w-full"
                    style="aspect-ratio: 16 / 9; object-fit: cover; background: var(--warm-200)"
                />
            </figure>
        @else
            {{-- No image — a single asterisk-divider band carries the transition
             from byline into the body. Tiny, restrained, doesn't pretend to
             be an image slot. --}}
            <div
                class="mx-auto flex max-w-3xl items-center justify-center px-5 md:px-8"
                style="margin-top: clamp(2.5rem, 5vw, 3.75rem); margin-bottom: clamp(2.5rem, 5vw, 3.75rem); gap: 1rem"
            >
                <span
                    aria-hidden="true"
                    style="display: inline-block; width: 3rem; height: 1px; background: var(--warm-300)"
                ></span>
                <span
                    class="font-display"
                    style="color: var(--warm-500); font-size: 1.25rem; line-height: 1; font-weight: 500"
                >&#x2733;</span>
                <span
                    aria-hidden="true"
                    style="display: inline-block; width: 3rem; height: 1px; background: var(--warm-300)"
                ></span>
            </div>
        @endif

        {{-- ═══ Body column ═══ --}}
        <div class="mx-auto px-5 md:px-8" style="max-width: 42rem; padding-bottom: clamp(2.5rem, 4vw, 3.5rem)">
            {{-- Lede / standfirst: the excerpt set as a magazine-style opening
             paragraph — italic, larger display, with a thin gold left rule
             marking it as the deck. Functions as the "what this is about"
             before the body proper begins. --}}
            @if ($post->excerpt)
                <p
                    class="font-display italic"
                    style="
                        color: var(--warm-700);
                        font-size: clamp(1.375rem, 2vw, 1.75rem);
                        line-height: 1.36;
                        font-weight: 400;
                        margin-bottom: 2.5rem;
                        padding-left: 1.25rem;
                        border-left: 2px solid var(--warm-500);
                    "
                >
                    {{ $post->excerpt }}
                </p>
            @endif

            {{-- Body: paragraph-wrapped, drop cap on the first paragraph,
             generous line-height tuned for long-form reading. --}}
            <div class="article-body font-body" style="color: var(--warm-700); font-size: 1.0625rem; line-height: 1.72">
                @foreach ($paragraphs as $paragraph)
                    <p>{!! $paragraph !!}</p>
                @endforeach
            </div>

            {{-- End-of-article flourish: short hairline + asterisk + short hairline,
             centered. The same Pophams divider mark used elsewhere. --}}
            <div
                class="flex items-center justify-center"
                style="margin-top: 2.5rem; margin-bottom: 1.25rem; gap: 0.875rem"
            >
                <span
                    aria-hidden="true"
                    style="display: inline-block; width: 2.5rem; height: 1px; background: var(--warm-300)"
                ></span>
                <span
                    class="font-display"
                    style="color: var(--warm-500); font-size: 1.125rem; line-height: 1; font-weight: 500"
                >&#x2733;</span>
                <span
                    aria-hidden="true"
                    style="display: inline-block; width: 2.5rem; height: 1px; background: var(--warm-300)"
                ></span>
            </div>

            @if (count($tagList) > 0)
                <p
                    class="font-body text-center uppercase"
                    style="font-size: 0.6875rem; letter-spacing: 0.22em; color: var(--warm-700); opacity: 0.65"
                >
                    Filed under
                    <span style="font-weight: 600; opacity: 1; color: var(--warm-700)">{{ implode(' · ', $tagList) }}</span>
                </p>
            @endif

            {{-- Author signature — left-aligned credit line, restrained. The
             "read more" CTA is intentionally omitted because the related-posts
             section directly below already serves that purpose. --}}
            <div
                style="
                    margin-top: 2.5rem;
                    padding-top: 1.5rem;
                    border-top: 1px solid color-mix(in oklab, var(--warm-700) 18%, transparent);
                "
            >
                <p
                    class="font-body uppercase"
                    style="
                        font-size: 0.6875rem;
                        letter-spacing: 0.22em;
                        color: var(--warm-700);
                        opacity: 0.6;
                        margin-bottom: 0.375rem;
                    "
                >
                    Written by
                </p>
                <p
                    class="font-display"
                    style="
                        color: var(--warm-700);
                        font-size: 1.25rem;
                        font-weight: 500;
                        line-height: 1.2;
                        margin-bottom: 0.375rem;
                    "
                >
                    {{ $authorName }}
                </p>
                <p style="color: var(--warm-700); font-size: 0.9375rem; line-height: 1.55; opacity: 0.85">
                    Notes, recipes, and updates from {{ $settings->store->name ?? 'our kitchen' }}.
                </p>
            </div>
        </div>
    </article>

    {{-- ═══ Related Reading ═══ --}}
    @if ($relatedPosts->isNotEmpty())
        <section
            style="
                background: var(--warm-100);
                border-top: 1px solid color-mix(in oklab, var(--warm-700) 22%, transparent);
            "
        >
            <div
                class="mx-auto max-w-6xl px-5 md:px-8"
                style="padding-top: clamp(1.75rem, 3vw, 2.5rem); padding-bottom: clamp(2.5rem, 4.5vw, 4rem)"
            >
                <div class="mb-8 flex items-center gap-3" style="color: var(--warm-700)">
                    <span
                        aria-hidden="true"
                        style="display: inline-block; width: 1.75rem; height: 1px; background: var(--warm-500)"
                    ></span>
                    <span class="font-body font-semibold uppercase" style="font-size: 0.6875rem; letter-spacing: 0.24em"
                        >More from the kitchen</span>
                </div>

                {{-- Text-first cards. With-image posts get a compact 16:10 ratio
                 (proportioned for a card, not a hero); image-less posts skip
                 the image slot entirely and rely on a top hairline + tag
                 eyebrow + display-font title — looks intentional, not absent. --}}
                <div class="grid md:grid-cols-3" style="row-gap: 2.5rem; column-gap: clamp(2.5rem, 4vw, 4rem)">
                    @foreach ($relatedPosts as $related)
                        <a
                            href="{{ route('storefront.blog.show', $related) }}"
                            class="group block"
                            style="
                                color: var(--warm-700);
                                padding-top: 1.25rem;
                                border-top: 1px solid color-mix(in oklab, var(--warm-700) 18%, transparent);
                            "
                        >
                            @if ($related->featured_image)
                                <div style="overflow: hidden; margin-bottom: 1.125rem">
                                    <img
                                        src="{{ Storage::url($related->featured_image) }}"
                                        alt="{{ $related->title }}"
                                        style="
                                            width: 100%;
                                            aspect-ratio: 16 / 10;
                                            object-fit: cover;
                                            transition: transform 600ms cubic-bezier(0.22, 1, 0.36, 1);
                                        "
                                        class="group-hover:scale-[1.03]"
                                    />
                                </div>
                            @endif

                            @php $relatedTag = is_array($related->tags) ? ($related->tags[0] ?? null) : null; @endphp
                            @if ($relatedTag)
                                <p
                                    class="font-body uppercase"
                                    style="
                                        font-size: 0.625rem;
                                        letter-spacing: 0.22em;
                                        color: var(--warm-700);
                                        opacity: 0.65;
                                        margin-bottom: 0.5rem;
                                    "
                                >
                                    {{ $relatedTag }}
                                </p>
                            @endif
                            <h3
                                class="font-display group-hover:underline"
                                style="
                                    color: var(--warm-700);
                                    font-size: 1.25rem;
                                    font-weight: 500;
                                    line-height: 1.25;
                                    text-underline-offset: 4px;
                                "
                            >
                                {{ $related->title }}
                            </h3>
                            @if ($related->excerpt)
                                <p
                                    style="
                                        color: var(--warm-700);
                                        font-size: 0.9375rem;
                                        line-height: 1.55;
                                        margin-top: 0.5rem;
                                        opacity: 0.85;
                                    "
                                >
                                    {{ Str::limit($related->excerpt, 110) }}
                                </p>
                            @endif
                            <p
                                class="font-body uppercase"
                                style="
                                    font-size: 0.625rem;
                                    letter-spacing: 0.18em;
                                    color: var(--warm-700);
                                    opacity: 0.55;
                                    margin-top: 0.75rem;
                                "
                            >
                                {{ $related->published_at?->format('F j, Y') }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.storefront>
