<x-filament-panels::page>
    @php
        $topics = $this->getTopics();
        $topicIcons = [
            'Getting Started' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.841m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />',
            'Managing Orders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />',
            'Storefront' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />',
            'Finances' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />',
            'Marketing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a1.125 1.125 0 0 1-1.577-.506 20.1 20.1 0 0 1-1.228-4.218m3.85-8.1a20.137 20.137 0 0 1 1.228-4.218 1.125 1.125 0 0 1 1.577-.506l.657.38c.524.3.71.96.463 1.51a19.87 19.87 0 0 0-.985 2.784m-3.85 8.099a20.37 20.37 0 0 0 3.85-8.099" />',
            'Tools' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" />',
            'Billing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />',
            'FAQ' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />',
        ];
        $topicColors = [
            'Getting Started' => '#d4920c',
            'Managing Orders' => '#8B5E3C',
            'Storefront' => '#2d8a5e',
            'Finances' => '#6366f1',
            'Marketing' => '#e85d75',
            'Tools' => '#0891b2',
            'Billing' => '#7c3aed',
            'FAQ' => '#d97706',
        ];
        $popularArticles = [
            ['topic' => 'Getting Started', 'title' => 'Setting Up Your Store'],
            ['topic' => 'Managing Orders', 'title' => 'Using Quick Order'],
            ['topic' => 'Storefront', 'title' => 'Customizing Your Theme'],
            ['topic' => 'Billing', 'title' => 'Plans & Pricing'],
        ];
    @endphp

    <style>
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
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 60px 60px;"></div>
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
                        @php $color = $topicColors[$pa['topic']] ?? '#8B5E3C'; @endphp
                        <div class="kb-article-card" onclick="document.querySelectorAll('[x-data]')[0].__x.$data.search = '{{ addslashes($pa['title']) }}'">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $color }};"></div>
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
                    @php $color = $topicColors[$topic['title']] ?? '#8B5E3C'; @endphp
                    <div class="kb-card" @click="openTopic = {{ $i }}">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background: {{ $color }}15;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="color: {{ $color }};">{!! $topicIcons[$topic['title']] ?? '' !!}</svg>
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
                    @php $color = $topicColors[$topic['title']] ?? '#8B5E3C'; @endphp
                    <div x-show="openTopic === {{ $i }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: {{ $color }}15;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" style="color: {{ $color }};">{!! $topicIcons[$topic['title']] ?? '' !!}</svg>
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
                        <div class="bg-white border border-brand-200/30 rounded-lg px-7 py-6 mb-3 transition-colors hover:border-brand-200/70">
                            <h4 class="font-semibold text-brand-900 m-0 mb-2.5 text-[0.95rem]">{{ $article['title'] }}</h4>
                            <div class="text-[0.85rem] text-brand-700 leading-loose">{!! $article['content'] !!}</div>
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
