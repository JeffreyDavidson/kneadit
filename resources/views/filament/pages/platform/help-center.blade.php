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
        .kb-grid { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 20px !important; }
        .kb-popular-grid { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important; }
        .kb-card { background: #fff; border: 1px solid rgba(212,165,116,0.15); border-radius: 12px; padding: 28px 24px; cursor: pointer; transition: all 0.2s; }
        .kb-card:hover { border-color: #d4920c; box-shadow: 0 8px 24px rgba(212,146,12,0.08); transform: translateY(-2px); }
        .kb-article-card { background: #fff; border: 1px solid rgba(212,165,116,0.15); border-radius: 10px; padding: 16px 20px; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; gap: 12px; }
        .kb-article-card:hover { border-color: #d4920c; background: #fffcf7; }
    </style>

    <div x-data="{
        search: '',
        openTopic: null,
        topics: {{ Js::from($topics) }},
        // Deep link: HelpCenter::$articlePath is bound to ?article=topic/slug.
        // On mount, find the matching topic index and open it.
        init() {
            const path = @js($this->articlePath);
            if (!path) return;
            const [topicSlug] = path.split('/');
            const idx = this.topics.findIndex(t => t.slug === topicSlug);
            if (idx >= 0) this.openTopic = idx;
            this.$nextTick(() => {
                const target = document.getElementById('article-' + path.replace('/', '-'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },
        get filteredTopics() {
            if (!this.search.trim()) return this.topics;
            const q = this.search.toLowerCase();
            return this.topics.map(topic => ({
                ...topic,
                articles: topic.articles.filter(a =>
                    a.title.toLowerCase().includes(q) || a.content.toLowerCase().includes(q)
                )
            })).filter(t => t.articles.length > 0);
        }
    }">

        {{-- Hero Search (Intercom/Stripe style) --}}
        <div class="bg-gradient-to-br from-brand-900 via-brand-700 to-brand-600 rounded-2xl px-12 py-14 mb-10 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04] bg-[radial-gradient(circle_at_20%_50%,white_1px,transparent_1px),radial-gradient(circle_at_80%_20%,white_1px,transparent_1px)] bg-[length:60px_60px]"></div>
            <div class="relative z-10">
                <h2 class="text-white text-3xl font-extrabold m-0 mb-1.5 tracking-tight">How can we help?</h2>
                <p class="text-white/55 m-0 mb-7 text-[0.9rem]">Search our knowledge base or browse topics below</p>
                <div class="relative max-w-[520px] mx-auto">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 absolute left-4 top-[15px] text-brand-500 pointer-events-none" />
                    <input x-model="search" @input="openTopic = null" type="text" placeholder="Search for articles..."
                        class="w-full pl-12 pr-5 py-[15px] rounded-xl border-0 bg-white text-[0.95rem] text-brand-900 outline-none shadow-xl" />
                </div>
            </div>
        </div>

        {{-- Search Results --}}
        <div x-show="search.trim() !== ''" x-cloak>
            <p class="text-[0.8rem] text-brand-500 m-0 mb-4 font-semibold uppercase tracking-wider">Search Results</p>
            <template x-for="(topic, ti) in filteredTopics" :key="ti">
                <div class="mb-5">
                    <p class="text-xs text-brand-600 font-bold m-0 mb-2 uppercase tracking-wider" x-text="topic.title"></p>
                    <template x-for="(article, ai) in topic.articles" :key="ai">
                        <div class="bg-white border border-brand-200/40 rounded-lg px-5 py-4 mb-2 transition-colors hover:border-honey">
                            <h4 class="font-semibold text-brand-900 m-0 mb-1.5 text-[0.9rem]" x-text="article.title"></h4>
                            <div class="text-[0.8rem] text-brand-700 leading-relaxed" x-html="article.content"></div>
                        </div>
                    </template>
                </div>
            </template>
            <div x-show="filteredTopics.length === 0" class="text-center py-10 text-brand-500">
                <p class="m-0 font-semibold">No results found</p>
                <p class="mt-1.5 m-0 text-[0.85rem]">Try different keywords</p>
            </div>
        </div>

        {{-- Main Content (no search) --}}
        <div x-show="search.trim() === '' && openTopic === null">

            {{-- Popular Articles (Slack style) --}}
            <div class="mb-9">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-500 m-0 mb-3.5">Popular Articles</h3>
                <div class="kb-popular-grid">
                    @foreach ($popularArticles as $pa)
                        @php
                            [$topicSlug] = explode('/', $pa['slug']);
                            $topicMeta = collect($topics)->firstWhere('slug', $topicSlug);
                            $colorClass = $topicMeta ? 'bg-' . $topicMeta['color'] : 'bg-brand-600';
                            $idx = collect($topics)->search(fn ($t) => $t['slug'] === $topicSlug);
                        @endphp
                        <div class="kb-article-card" @click="search = ''; openTopic = {{ (int) $idx }}; $nextTick(() => { const t = document.getElementById('article-{{ str_replace('/', '-', $pa['slug']) }}'); if (t) t.scrollIntoView({behavior:'smooth',block:'start'}); })">
                            <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $colorClass }}"></div>
                            <div>
                                <p class="m-0 text-sm font-semibold text-brand-900">{{ $pa['title'] }}</p>
                                <p class="mt-0.5 m-0 text-xs text-brand-500">{{ $pa['topic'] }}</p>
                            </div>
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-brand-400 ml-auto flex-shrink-0" />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Category Grid (Intercom style) --}}
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-500 m-0 mb-3.5">Browse by Topic</h3>
            <div class="kb-grid">
                @foreach ($topics as $i => $topic)
                    @php $c = $cls($topic); @endphp
                    <div class="kb-card" @click="openTopic = {{ $i }}">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 {{ $c['tile'] }}">
                            <x-filament::icon :icon="$topic['icon']" class="w-6 h-6 {{ $c['text'] }}" />
                        </div>
                        <h3 class="m-0 mb-1 text-base font-bold text-brand-900">{{ $topic['title'] }}</h3>
                        <p class="m-0 mb-3 text-[0.8rem] text-brand-500">{{ count($topic['articles']) }} articles</p>
                        <ul class="list-none p-0 m-0">
                            @foreach (array_slice($topic['articles'], 0, 2) as $article)
                                <li class="text-[0.8rem] text-brand-700 py-1 flex items-center gap-1.5">
                                    <x-heroicon-o-chevron-right class="w-3 h-3 text-brand-400 flex-shrink-0" stroke-width="2" />
                                    {{ $article['title'] }}
                                </li>
                            @endforeach
                            @if (count($topic['articles']) > 2)
                                <li class="text-xs text-honey py-1 font-semibold">+ {{ count($topic['articles']) - 2 }} more</li>
                            @endif
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Expanded Topic (Notion style — clean article list) --}}
        <div x-show="openTopic !== null && search.trim() === ''" x-cloak>
            <div class="flex items-center gap-3.5 mb-7 pb-5 border-b border-brand-200/30">
                <button @click="openTopic = null"
                    class="inline-flex items-center justify-center w-[34px] h-[34px] rounded-lg bg-transparent border border-brand-200/40 cursor-pointer transition-colors hover:bg-brand-200/10 flex-shrink-0">
                    <x-heroicon-o-arrow-left class="w-4 h-4 text-brand-600" stroke-width="2" />
                </button>
                @foreach ($topics as $i => $topic)
                    @php $c = $cls($topic); @endphp
                    <div x-show="openTopic === {{ $i }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $c['tile'] }}">
                            <x-filament::icon :icon="$topic['icon']" class="w-5 h-5 {{ $c['text'] }}" />
                        </div>
                        <div>
                            <h3 class="m-0 text-[1.15rem] font-bold text-brand-900">{{ $topic['title'] }}</h3>
                            <p class="mt-0.5 m-0 text-xs text-brand-500">{{ count($topic['articles']) }} articles</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @foreach ($topics as $i => $topic)
                <div x-show="openTopic === {{ $i }}">
                    @foreach ($topic['articles'] as $j => $article)
                        <div id="article-{{ $topic['slug'] }}-{{ $article['slug'] }}" class="bg-white border border-brand-200/30 rounded-lg px-7 py-6 mb-3 transition-colors hover:border-brand-200/70">
                            <h4 class="font-semibold text-brand-900 m-0 mb-2.5 text-[0.95rem]">{{ $article['title'] }}</h4>
                            <div class="prose prose-sm max-w-none text-[0.85rem] text-brand-700 leading-loose">{!! $article['content'] !!}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Contact Footer --}}
        <div class="mt-10 p-8 rounded-xl bg-[#fffcf7] border border-brand-200/30 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="m-0 mb-1 text-base font-bold text-brand-900">Can't find what you need?</h3>
                <p class="m-0 text-brand-700 text-[0.85rem]">Our team responds within 24 hours.</p>
            </div>
            <a href="mailto:support@getkneadit.app"
                class="inline-flex items-center gap-2 bg-brand-900 hover:bg-brand-700 text-white font-semibold no-underline px-6 py-2.5 rounded-xl text-sm transition-colors">
                <x-heroicon-o-envelope class="w-[18px] h-[18px]" />
                Contact Support
            </a>
        </div>
    </div>
</x-filament-panels::page>
