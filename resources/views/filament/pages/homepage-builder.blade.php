<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Action buttons --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-filament::button wire:click="save" color="primary" icon="heroicon-o-check">
                    Save Changes
                </x-filament::button>
                <x-filament::button wire:click="resetToDefaults" color="gray" icon="heroicon-o-arrow-path"
                    wire:confirm="Are you sure you want to reset all homepage sections to their defaults?">
                    Reset to Defaults
                </x-filament::button>
            </div>
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M11.47 7.72a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 0 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" /></svg>
                        </button>
                        <button wire:click="moveDown('{{ $key }}')" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 disabled:opacity-30"
                            @if (($config['order'] ?? 1) >= count($this->sections)) disabled @endif>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>

                    {{-- Section info --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $meta['label'] }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
                    </div>

                    {{-- Toggle --}}
                    <div class="flex items-center gap-3">
                        @if (!in_array($key, ['hero', 'about', 'social']) || $key === 'social')
                        <button @click="expanded = !expanded" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                        @endif

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer"
                                   wire:click="toggleVisibility('{{ $key }}')"
                                   {{ $isVisible ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>

                {{-- Expandable settings --}}
                @if ($key !== 'hero' && $key !== 'about')
                <div x-show="expanded" x-collapse class="border-t border-gray-100 dark:border-gray-800 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                        @switch($key)
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
                                            <option value="{{ $opt }}" {{ ($config['count'] ?? 6) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                            <option value="{{ $opt }}" {{ ($config['count'] ?? 3) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                            <option value="{{ $opt }}" {{ ($config['count'] ?? 4) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                            <option value="{{ $opt }}" {{ ($config['count'] ?? 3) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                            <option value="{{ $val }}" {{ ($config['button_link'] ?? 'order') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @break

                            @case('social')
                                <div class="col-span-full">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Social links are managed in <a href="{{ \App\Filament\Pages\ManageSettings::getUrl() }}" class="text-primary-600 hover:underline">Settings</a>. Use the toggle to show/hide this section.</p>
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
