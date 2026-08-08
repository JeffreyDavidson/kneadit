<x-filament-panels::page>
    @php
        $topics = $this->getTopics();
        $popularArticles = $this->getPopularArticles();
        // The Alpine state below uses each topic's `slug` to deep-link from the
        // article query string. Each topic carries its own `color` token from
        // config/help.php — used for both the dot indicator and the tile.
        $cls = fn (array $topic): array => [
            'dot' => 'bg-' . $topic['color'],
            'text' => 'text-' . $topic['color'],
            'tile' => 'bg-' . $topic['color'] . '/15',
        ];
    @endphp

    <style @cspnonce>
        .kb-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 20px !important;
        }
        .kb-popular-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
        .kb-card {
            background: #fff;
            border: 1px solid rgba(212, 165, 116, 0.15);
            border-radius: 12px;
            padding: 28px 24px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .kb-card:hover {
            border-color: #d4920c;
            box-shadow: 0 8px 24px rgba(212, 146, 12, 0.08);
            transform: translateY(-2px);
        }
        .kb-article-card {
            background: #fff;
            border: 1px solid rgba(212, 165, 116, 0.15);
            border-radius: 10px;
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .kb-article-card:hover {
            border-color: #d4920c;
            background: #fffcf7;
        }
    </style>

    <div
        x-data="{
        search: '',
        openTopic: null,
        topics: {{ Js::from($topics) }},
        // Deep link: HelpCenter::$articlePath is bound to ?article=topic/slug.
        // On mount, find the matching topic index and open it.
        init() {
            const path = @js($this->articlePath);
            if (! path) return;
            const [topicSlug] = path.split('/');
            const idx = this.topics.findIndex(t => t.slug === topicSlug);
            if (idx >= 0) this.openTopic = idx;
            this.$nextTick(() => {
                const target = document.getElementById('article-' + path.replace('/', '-'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        get filteredTopics() {
            if (! this.search.trim()) return this.topics;
            const q = this.search.toLowerCase();
            return this.topics.map(topic => ({
                ...topic,
                articles: topic.articles.filter(a =>
                    a.title.toLowerCase().includes(q) || a.content.toLowerCase().includes(q)
                )
            })).filter(t => t.articles.length > 0);
        }
    }"
    >
        {{-- Hero Search (Intercom/Stripe style) --}}
        <div class="from-brand-900 via-brand-700 to-brand-600 relative mb-10 overflow-hidden rounded-2xl bg-gradient-to-br px-12 py-14 text-center">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_50%,white_1px,transparent_1px),radial-gradient(circle_at_80%_20%,white_1px,transparent_1px)] bg-[length:60px_60px] opacity-[0.04]"></div>
            <div class="relative z-10">
                <h2 class="m-0 mb-1.5 text-3xl font-extrabold tracking-tight text-white">How can we help?</h2>
                <p class="m-0 mb-7 text-[0.9rem] text-white/55">Search our knowledge base or browse topics below</p>
                <div class="relative mx-auto max-w-[520px]">
                    <x-heroicon-o-magnifying-glass class="text-brand-500 pointer-events-none absolute top-[15px] left-4 h-5 w-5" />
                    <input
                        x-model="search"
                        @input="openTopic = null"
                        type="text"
                        placeholder="Search for articles..."
                        class="text-brand-900 w-full rounded-xl border-0 bg-white py-[15px] pr-5 pl-12 text-[0.95rem] shadow-xl outline-none"
                    />
                </div>
            </div>
        </div>

        {{-- Search Results --}}
        <div x-show="search.trim() !== ''" x-cloak>
            <p class="text-brand-500 m-0 mb-4 text-[0.8rem] font-semibold tracking-wider uppercase">Search Results</p>
            <template x-for="(topic, ti) in filteredTopics" :key="ti">
                <div class="mb-5">
                    <p
                        class="text-brand-600 m-0 mb-2 text-xs font-bold tracking-wider uppercase"
                        x-text="topic.title"
                    ></p>
                    <template x-for="(article, ai) in topic.articles" :key="ai">
                        <div class="border-brand-200/40 hover:border-honey mb-2 rounded-lg border bg-white px-5 py-4 transition-colors">
                            <h4
                                class="text-brand-900 m-0 mb-1.5 text-[0.9rem] font-semibold"
                                x-text="article.title"
                            ></h4>
                            <div class="text-brand-700 text-[0.8rem] leading-relaxed" x-html="article.content"></div>
                        </div>
                    </template>
                </div>
            </template>
            <div x-show="filteredTopics.length === 0" class="text-brand-500 py-10 text-center">
                <p class="m-0 font-semibold">No results found</p>
                <p class="m-0 mt-1.5 text-[0.85rem]">Try different keywords</p>
            </div>
        </div>

        {{-- Main Content (no search) --}}
        <div x-show="search.trim() === '' && openTopic === null">
            {{-- Popular Articles (Slack style) --}}
            <div class="mb-9">
                <h3 class="text-brand-500 m-0 mb-3.5 text-xs font-bold tracking-wider uppercase">Popular Articles</h3>
                <div class="kb-popular-grid">
                    @foreach ($popularArticles as $pa)
                        @php
                            [$topicSlug] = explode('/', $pa['slug']);
                            $topicMeta = collect($topics)->firstWhere('slug', $topicSlug);
                            $colorClass = $topicMeta ? 'bg-' . $topicMeta['color'] : 'bg-brand-600';
                            $idx = collect($topics)->search(fn ($t) => $t['slug'] === $topicSlug);
                        @endphp
                        <div
                            class="kb-article-card"
                            @click="search = ''; openTopic = {{ (int) $idx }}; $nextTick(() => { const t = document.getElementById('article-{{ str_replace('/', '-', $pa['slug']) }}'); if (t) t.scrollIntoView({behavior:'smooth',block:'start'}); })"
                        >
                            <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $colorClass }}"></div>
                            <div>
                                <p class="text-brand-900 m-0 text-sm font-semibold">{{ $pa['title'] }}</p>
                                <p class="text-brand-500 m-0 mt-0.5 text-xs">{{ $pa['topic'] }}</p>
                            </div>
                            <x-heroicon-o-chevron-right class="text-brand-400 ml-auto h-4 w-4 flex-shrink-0" />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Category Grid (Intercom style) --}}
            <h3 class="text-brand-500 m-0 mb-3.5 text-xs font-bold tracking-wider uppercase">Browse by Topic</h3>
            <div class="kb-grid">
                @foreach ($topics as $i => $topic)
                    @php $c = $cls($topic); @endphp
                    <div class="kb-card" @click="openTopic = {{ $i }}">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 {{ $c['tile'] }}">
                            <x-filament::icon :icon="$topic['icon']" class="w-6 h-6 {{ $c['text'] }}" />
                        </div>
                        <h3 class="text-brand-900 m-0 mb-1 text-base font-bold">{{ $topic['title'] }}</h3>
                        <p class="text-brand-500 m-0 mb-3 text-[0.8rem]">{{ count($topic['articles']) }} articles</p>
                        <ul class="m-0 list-none p-0">
                            @foreach (array_slice($topic['articles'], 0, 2) as $article)
                                <li class="text-brand-700 flex items-center gap-1.5 py-1 text-[0.8rem]">
                                    <x-heroicon-o-chevron-right
                                        class="text-brand-400 h-3 w-3 flex-shrink-0"
                                        stroke-width="2"
                                    />
                                    {{ $article['title'] }}
                                </li>
                            @endforeach
                            @if (count($topic['articles']) > 2)
                                <li class="text-honey py-1 text-xs font-semibold">
                                    + {{ count($topic['articles']) - 2 }} more
                                </li>
                            @endif
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Expanded Topic (Notion style — clean article list) --}}
        <div x-show="openTopic !== null && search.trim() === ''" x-cloak>
            <div class="border-brand-200/30 mb-7 flex items-center gap-3.5 border-b pb-5">
                <button
                    @click="openTopic = null"
                    class="border-brand-200/40 hover:bg-brand-200/10 inline-flex h-[34px] w-[34px] flex-shrink-0 cursor-pointer items-center justify-center rounded-lg border bg-transparent transition-colors"
                >
                    <x-heroicon-o-arrow-left class="text-brand-600 h-4 w-4" stroke-width="2" />
                </button>
                @foreach ($topics as $i => $topic)
                    @php $c = $cls($topic); @endphp
                    <div x-show="openTopic === {{ $i }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $c['tile'] }}">
                            <x-filament::icon :icon="$topic['icon']" class="w-5 h-5 {{ $c['text'] }}" />
                        </div>
                        <div>
                            <h3 class="text-brand-900 m-0 text-[1.15rem] font-bold">{{ $topic['title'] }}</h3>
                            <p class="text-brand-500 m-0 mt-0.5 text-xs">{{ count($topic['articles']) }} articles</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @foreach ($topics as $i => $topic)
                <div x-show="openTopic === {{ $i }}">
                    @foreach ($topic['articles'] as $j => $article)
                        <div
                            id="article-{{ $topic['slug'] }}-{{ $article['slug'] }}"
                            class="border-brand-200/30 hover:border-brand-200/70 mb-3 rounded-lg border bg-white px-7 py-6 transition-colors"
                        >
                            <h4 class="text-brand-900 m-0 mb-2.5 text-[0.95rem] font-semibold">
                                {{ $article['title'] }}
                            </h4>
                            <div class="prose prose-sm text-brand-700 max-w-none text-[0.85rem] leading-loose">
                                {!! $article['content'] !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Contact Footer --}}
        <div class="border-brand-200/30 mt-10 flex flex-wrap items-center justify-between gap-4 rounded-xl border bg-[#fffcf7] p-8">
            <div>
                <h3 class="text-brand-900 m-0 mb-1 text-base font-bold">Can't find what you need?</h3>
                <p class="text-brand-700 m-0 text-[0.85rem]">Our team responds within 24 hours.</p>
            </div>
            <a
                href="mailto:support@getkneadit.app"
                class="bg-brand-900 hover:bg-brand-700 inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white no-underline transition-colors"
            >
                <x-heroicon-o-envelope class="h-[18px] w-[18px]" />
                Contact Support
            </a>
        </div>
    </div>
</x-filament-panels::page>
