<x-filament-panels::page>
    <div x-data="{
        search: '',
        openTopic: null,
        get filteredTopics() {
            if (!this.search.trim()) return {{ Js::from($this->getTopics()) }};
            const q = this.search.toLowerCase();
            return {{ Js::from($this->getTopics()) }}.map(topic => ({
                ...topic,
                articles: topic.articles.filter(a =>
                    a.title.toLowerCase().includes(q) || a.content.toLowerCase().includes(q)
                )
            })).filter(t => t.articles.length > 0);
        }
    }" x-init="
        document.addEventListener('keydown', e => {
            if ((e.metaKey || e.ctrlKey) && e.key === '?') {
                e.preventDefault();
                $refs.searchInput.focus();
            }
        });
    " class="space-y-6">

        {{-- Search --}}
        <div class="relative">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-5 h-5 absolute left-3 top-3 text-gray-400 pointer-events-none" />
            <input x-ref="searchInput" x-model="search" type="text"
                placeholder="Search help articles... (Ctrl+? to focus)"
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" />
        </div>

        {{-- Topics --}}
        <template x-for="(topic, ti) in filteredTopics" :key="ti">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="openTopic = openTopic === ti ? null : ti"
                    class="w-full flex items-center gap-3 p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-950 flex items-center justify-center flex-shrink-0">
                        <x-filament::icon x-bind:icon="topic.icon" class="w-5 h-5 text-primary-500" />
                    </div>
                    <div class="flex-1">
                        <span class="font-semibold" x-text="topic.title"></span>
                        <span class="text-sm text-gray-500 ml-2" x-text="topic.articles.length + ' article' + (topic.articles.length !== 1 ? 's' : '')"></span>
                    </div>
                    <x-filament::icon icon="heroicon-o-chevron-down"
                        class="w-5 h-5 text-gray-400 transition-transform duration-200"
                        x-bind:class="openTopic === ti ? 'rotate-180' : ''" />
                </button>

                <div x-show="openTopic === ti || search.trim() !== ''" x-collapse>
                    <div class="border-t border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="(article, ai) in topic.articles" :key="ai">
                            <div class="p-4 pl-16">
                                <h4 class="font-medium mb-2" x-text="article.title"></h4>
                                <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed prose prose-sm dark:prose-invert max-w-none" x-html="article.content"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty state --}}
        <div x-show="filteredTopics.length === 0" class="text-center py-12 text-gray-500">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-12 h-12 mx-auto mb-3 opacity-30" />
            <p>No articles match your search.</p>
        </div>

        {{-- Contact Support --}}
        <div class="text-center pt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-gray-500 mb-2">Can't find what you're looking for?</p>
            <a href="mailto:support@kneadit.app" class="inline-flex items-center gap-2 text-primary-500 hover:text-primary-600 font-medium">
                <x-filament::icon icon="heroicon-o-envelope" class="w-5 h-5" />
                Contact Support
            </a>
        </div>
    </div>
</x-filament-panels::page>
