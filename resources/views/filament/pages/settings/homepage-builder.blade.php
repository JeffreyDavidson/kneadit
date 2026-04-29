<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Action buttons --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-filament::button wire:click="save" color="primary" icon="heroicon-o-check">
                    Save Changes
                </x-filament::button>
                {{ $this->resetToDefaultsAction }}
            </div>
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                <x-heroicon-o-eye class="w-4 h-4" />
                Preview Storefront
            </a>
        </div>

        {{-- Section Cards --}}
        @foreach (collect($this->sections)->sortBy('order') as $key => $config)
            @php
                $meta = $this->getSectionMeta($key);
                $isVisible = $config['visible'] ?? true;
            @endphp
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 {{ !$isVisible ? 'opacity-60' : '' }}"
                 x-data="{ expanded: false }">
                <div class="p-4 flex items-center gap-4">
                    {{-- Reorder arrows --}}
                    <div class="flex flex-col gap-0.5">
                        <button wire:click="moveUp('{{ $key }}')" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30"
                            @if (($config['order'] ?? 1) <= 1) disabled @endif>
                            <x-heroicon-s-chevron-up class="w-4 h-4" />
                        </button>
                        <button wire:click="moveDown('{{ $key }}')" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30"
                            @if (($config['order'] ?? 1) >= count($this->sections)) disabled @endif>
                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                        </button>
                    </div>

                    {{-- Section info --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $meta['label'] }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
                    </div>

                    {{-- Toggle --}}
                    <div class="flex items-center gap-3">
                        @if (!in_array($key, ['about']))
                        <button @click="expanded = !expanded" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                        </button>
                        @endif

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer"
                                   wire:click="toggleVisibility('{{ $key }}')"
                                   @checked($isVisible)>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>

                {{-- Expandable settings --}}
                @if ($key !== 'about')
                <div x-show="expanded" x-collapse class="border-t border-gray-100 dark:border-gray-800 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                        @switch($key)
                            @case('hero')
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tagline</label>
                                    <input type="text" wire:model.blur="hero_tagline"
                                           placeholder="Where every bite tells a story"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shown below your store name in the hero banner.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Button Text</label>
                                    <input type="text" wire:model.blur="hero_primary_cta_text"
                                           placeholder="Order Now"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Secondary Button Text</label>
                                    <input type="text" wire:model.blur="hero_secondary_cta_text"
                                           placeholder="Browse Menu"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                @break

                            @case('featured_products')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                           value="{{ $config['title'] ?? 'Our Favorites' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                           value="{{ $config['subtitle'] ?? 'Freshly made' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Product Count</label>
                                    <select wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        @foreach ([3, 6, 9] as $opt)
                                            <option value="{{ $opt }}" @selected(($config['count'] ?? 6) == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('categories')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                           value="{{ $config['title'] ?? 'What We Bake' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                           value="{{ $config['subtitle'] ?? 'Something for everyone' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                @break

                            @case('reviews')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                           value="{{ $config['title'] ?? 'Kind Words' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                           value="{{ $config['subtitle'] ?? 'What our customers say' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Review Count</label>
                                    <select wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        @foreach ([3, 6] as $opt)
                                            <option value="{{ $opt }}" @selected(($config['count'] ?? 3) == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('gallery')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                           value="{{ $config['title'] ?? 'Customer Gallery' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                           value="{{ $config['subtitle'] ?? 'Shared by our community' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Photo Count</label>
                                    <select wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        @foreach ([4, 8] as $opt)
                                            <option value="{{ $opt }}" @selected(($config['count'] ?? 4) == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('blog')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                           value="{{ $config['title'] ?? 'Latest Updates' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                           value="{{ $config['subtitle'] ?? 'From our kitchen' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Post Count</label>
                                    <select wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        @foreach ([3, 6] as $opt)
                                            <option value="{{ $opt }}" @selected(($config['count'] ?? 3) == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('cta')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Heading</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'heading', $event.target.value)"
                                           value="{{ $config['heading'] ?? 'Treat Yourself Today' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtext</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'subtext', $event.target.value)"
                                           value="{{ $config['subtext'] ?? '' }}"
                                           placeholder="Optional subtext (leave blank for default)"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Button Text</label>
                                    <input type="text" wire:change="updateSectionField('{{ $key }}', 'button_text', $event.target.value)"
                                           value="{{ $config['button_text'] ?? 'Start Your Order' }}"
                                           class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Button Link</label>
                                    <select wire:change="updateSectionField('{{ $key }}', 'button_link', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        @foreach (['order' => 'Order Page', 'menu' => 'Menu Page', 'contact' => 'Contact Page'] as $val => $label)
                                            <option value="{{ $val }}" @selected(($config['button_link'] ?? 'order') === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('social')
                                <div class="col-span-full">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Social links are managed in <a href="{{ \App\Filament\Pages\Settings\ManageSettings::getUrl() }}" class="text-primary-600 hover:underline">Settings</a>. Use the toggle to show/hide this section.</p>
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
