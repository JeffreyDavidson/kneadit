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
    " style="max-width: 900px; margin: 0 auto;">

        {{-- Search --}}
        <div style="position: relative; margin-bottom: 24px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px; position: absolute; left: 14px; top: 14px; color: #a08060; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input x-ref="searchInput" x-model="search" type="text"
                placeholder="Search help articles... (Ctrl+?)"
                style="width: 100%; padding: 12px 16px 12px 44px; border-radius: 12px; border: 1px solid rgba(212,165,116,0.3); background: #fff; font-size: 0.9rem; color: #3d2314; outline: none; box-shadow: 0 2px 8px rgba(61,35,20,0.04);"
                onfocus="this.style.borderColor='#d4920c';this.style.boxShadow='0 0 0 3px rgba(212,146,12,0.1)'"
                onblur="this.style.borderColor='rgba(212,165,116,0.3)';this.style.boxShadow='0 2px 8px rgba(61,35,20,0.04)'" />
        </div>

        {{-- Topics --}}
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <template x-for="(topic, ti) in filteredTopics" :key="ti">
                <div style="background: #fff; border-radius: 12px; border: 1px solid rgba(212,165,116,0.25); overflow: hidden; box-shadow: 0 2px 12px rgba(61,35,20,0.04);">
                    <button @click="openTopic = openTopic === ti ? null : ti"
                        style="width: 100%; display: flex; align-items: center; gap: 14px; padding: 16px 20px; text-align: left; cursor: pointer; border: none; background: transparent; transition: background 0.15s;"
                        onmouseover="this.style.background='rgba(212,165,116,0.06)'"
                        onmouseout="this.style.background='transparent'">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #8B5E3C, #D4A574); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px; color: white;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <div style="flex: 1;">
                            <span style="font-weight: 600; color: #3d2314; font-size: 0.95rem;" x-text="topic.title"></span>
                            <span style="font-size: 0.8rem; color: #a08060; margin-left: 8px;" x-text="topic.articles.length + ' article' + (topic.articles.length !== 1 ? 's' : '')"></span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; color: #a08060; transition: transform 0.2s;" x-bind:style="openTopic === ti ? 'transform: rotate(180deg); width: 18px; height: 18px; color: #a08060;' : 'width: 18px; height: 18px; color: #a08060;'"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>

                    <div x-show="openTopic === ti || search.trim() !== ''" x-collapse>
                        <div style="border-top: 1px solid rgba(212,165,116,0.15);">
                            <template x-for="(article, ai) in topic.articles" :key="ai">
                                <div style="padding: 16px 20px 16px 74px; border-bottom: 1px solid rgba(212,165,116,0.08);">
                                    <h4 style="font-weight: 600; color: #3d2314; margin: 0 0 8px 0; font-size: 0.9rem;" x-text="article.title"></h4>
                                    <div style="font-size: 0.85rem; color: #6b4c3b; line-height: 1.6;" x-html="article.content"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div x-show="filteredTopics.length === 0" style="text-align: center; padding: 48px 0; color: #a08060;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.3;"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <p style="margin: 0;">No articles match your search.</p>
        </div>

        {{-- Contact Support --}}
        <div style="margin-top: 32px; text-align: center; padding-top: 24px; border-top: 1px solid rgba(212,165,116,0.15);">
            <p style="margin: 0 0 8px 0; color: #a08060; font-size: 0.85rem;">Can't find what you're looking for?</p>
            <a href="mailto:support@getkneadit.app" style="display: inline-flex; align-items: center; gap: 8px; color: #d4920c; font-weight: 600; text-decoration: none; font-size: 0.9rem;"
                onmouseover="this.style.color='#8B5E3C'" onmouseout="this.style.color='#d4920c'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                Contact Support
            </a>
        </div>
    </div>
</x-filament-panels::page>
