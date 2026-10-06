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
            <a
                href="{{ route('home') }}"
                target="_blank"
                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 inline-flex items-center gap-1.5 text-sm font-medium"
            >
                <x-heroicon-o-eye class="h-4 w-4" />
                Preview Storefront
            </a>
        </div>

        {{-- Section Cards --}}
        @foreach (collect($this->sections)->sortBy('order') as $key => $config)
            @php
                $meta = $this->getSectionMeta($key);
                $isVisible = $config['visible'] ?? true;
            @endphp
            <div
                class="fi-section rounded-xl bg-(--kn-surface) shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 {{ !$isVisible ? 'opacity-60' : '' }}"
                x-data="{ expanded: false }"
            >
                <div class="flex items-center gap-4 p-4">
                    {{-- Reorder arrows --}}
                    <div class="flex flex-col gap-0.5">
                        <button
                            wire:click="moveUp('{{ $key }}')"
                            class="rounded p-1 text-(--kn-muted) hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            @if (($config['order'] ?? 1) <= 1) disabled @endif
                        >
                            <x-heroicon-s-chevron-up class="h-4 w-4" />
                        </button>
                        <button
                            wire:click="moveDown('{{ $key }}')"
                            class="rounded p-1 text-(--kn-muted) hover:bg-gray-100 hover:text-gray-600 disabled:opacity-30 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            @if (($config['order'] ?? 1) >= count($this->sections)) disabled @endif
                        >
                            <x-heroicon-s-chevron-down class="h-4 w-4" />
                        </button>
                    </div>

                    {{-- Section info --}}
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-(--kn-ink)">{{ $meta['label'] }}</h3>
                        <p class="text-xs text-(--kn-muted)">{{ $meta['description'] }}</p>
                    </div>

                    {{-- Toggle --}}
                    <div class="flex items-center gap-3">
                        @if (! in_array($key, ['about']))
                            <button @click="expanded = ! expanded"
                            class="rounded-lg p-1.5 text-(--kn-muted) hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                        >
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                        </button>
                    @endif

                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            class="peer sr-only"
                            wire:click="toggleVisibility('{{ $key }}')"
                            @checked($isVisible) />
                            <div class="peer peer-checked:bg-primary-600 h-5 w-9 rounded-full bg-(--kn-surface-hover) peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-(--kn-surface) after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full dark:border-gray-600"></div>
                        </label>
                    </div>
                </div>

                {{-- Expandable settings --}}
                @if ($key !== 'about')
                    <div x-show="expanded" x-collapse class="border-t border-(--kn-border) p-4">
                        <div class="grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2">
                            @switch ($key)
                                @case ('hero')
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Tagline</label>
                                        <input
                                            type="text"
                                            wire:model.blur="hero_tagline"
                                            placeholder="Where every bite tells a story"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                        <p class="mt-1 text-xs text-(--kn-muted)">
                                            Shown below your store name in the hero banner.
                                        </p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Primary Button Text</label>
                                        <input
                                            type="text"
                                            wire:model.blur="hero_primary_cta_text"
                                            placeholder="Order Now"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Secondary Button Text</label>
                                        <input
                                            type="text"
                                            wire:model.blur="hero_secondary_cta_text"
                                            placeholder="Browse Menu"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label for="hero-style" class="mb-1 block text-xs font-medium text-(--kn-ink-2)"
                                            >Hero Style</label>
                                        <select
                                            id="hero-style"
                                            wire:model="hero_style"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach (\App\Enums\Storefront\HeroStyle::cases() as $style)
                                                <option value="{{ $style->value }}">{{ $style->getLabel() }}</option>
                                            @endforeach
                                        </select>
                                        @error('hero_style')
                                            <p class="text-danger-600 mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        @include('filament.pages.settings.partials.hero-image-field', ['image' => \App\Enums\Storefront\StorefrontHeroImage::Homepage])
                                        <p class="mt-1 text-xs text-(--kn-muted)">
                                            JPG, PNG or WebP, up to 5 MB. Used as the homepage hero photo. Save to
                                            apply.
                                        </p>
                                    </div>
                                    @break
                                @case ('featured_products')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Section Title</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                            value="{{ $config['title'] ?? 'Our Favorites' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtitle</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                            value="{{ $config['subtitle'] ?? 'Freshly made' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Product Count</label>
                                        <select
                                            wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach ([3, 6, 9] as $opt)
                                                <option value="{{ $opt }}" @selected(($config['count'] ?? 6) == $opt)>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @case ('categories')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Section Title</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                            value="{{ $config['title'] ?? 'What We Bake' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtitle</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                            value="{{ $config['subtitle'] ?? 'Something for everyone' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    @break
                                @case ('reviews')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Section Title</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                            value="{{ $config['title'] ?? 'Kind Words' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtitle</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                            value="{{ $config['subtitle'] ?? 'What our customers say' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Review Count</label>
                                        <select
                                            wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach ([3, 6] as $opt)
                                                <option value="{{ $opt }}" @selected(($config['count'] ?? 3) == $opt)>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @case ('gallery')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Section Title</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                            value="{{ $config['title'] ?? 'Customer Gallery' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtitle</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                            value="{{ $config['subtitle'] ?? 'Shared by our community' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Photo Count</label>
                                        <select
                                            wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach ([4, 8] as $opt)
                                                <option value="{{ $opt }}" @selected(($config['count'] ?? 4) == $opt)>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @case ('blog')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Section Title</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'title', $event.target.value)"
                                            value="{{ $config['title'] ?? 'Latest Updates' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtitle</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtitle', $event.target.value)"
                                            value="{{ $config['subtitle'] ?? 'From our kitchen' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Post Count</label>
                                        <select
                                            wire:change="updateSectionField('{{ $key }}', 'count', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach ([3, 6] as $opt)
                                                <option value="{{ $opt }}" @selected(($config['count'] ?? 3) == $opt)>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @case ('cta')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Heading</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'heading', $event.target.value)"
                                            value="{{ $config['heading'] ?? 'Treat Yourself Today' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Subtext</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'subtext', $event.target.value)"
                                            value="{{ $config['subtext'] ?? '' }}"
                                            placeholder="Optional subtext (leave blank for default)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Button Text</label>
                                        <input
                                            type="text"
                                            wire:change="updateSectionField('{{ $key }}', 'button_text', $event.target.value)"
                                            value="{{ $config['button_text'] ?? 'Start Your Order' }}"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Button Link</label>
                                        <select
                                            wire:change="updateSectionField('{{ $key }}', 'button_link', $event.target.value)"
                                            class="fi-input block w-full rounded-lg border-(--kn-border) px-3 py-2 text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach (['order' => 'Order Page', 'menu' => 'Menu Page', 'contact' => 'Contact Page'] as $val => $label)
                                                <option
                                                    value="{{ $val }}"
                                                    @selected(($config['button_link'] ?? 'order') === $val)
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @case ('social')
                                    <div class="col-span-full">
                                        <p class="text-xs text-(--kn-muted)">
                                            Social links are managed in
                                            <a
                                                href="{{ \App\Filament\Pages\Settings\ManageSettings::getUrl() }}"
                                                class="text-primary-600 hover:underline"
                                                >Settings</a
                                            >. Use the toggle to show/hide this section.
                                        </p>
                                    </div>
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Page hero images --}}
        <div
            class="fi-section rounded-xl bg-(--kn-surface) shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10"
            x-data="{ expanded: false }"
        >
            <div class="flex items-center gap-4 p-4">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-(--kn-ink)">Page Heroes</h3>
                    <p class="text-xs text-(--kn-muted)">
                        Banner photos for the catering, rewards, and gift cards pages
                    </p>
                </div>
                <button
                    @click="expanded = ! expanded"
                    type="button"
                    class="rounded-lg p-1.5 text-(--kn-muted) hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                >
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                </button>
            </div>
            <div x-show="expanded" x-collapse class="border-t border-(--kn-border) p-4">
                <div class="grid max-w-2xl grid-cols-1 gap-4">
                    @foreach ([\App\Enums\Storefront\StorefrontHeroImage::Catering, \App\Enums\Storefront\StorefrontHeroImage::Loyalty, \App\Enums\Storefront\StorefrontHeroImage::GiftCards] as $pageHero)
                        @include('filament.pages.settings.partials.hero-image-field', ['image' => $pageHero])
                    @endforeach
                    <p class="text-xs text-(--kn-muted)">
                        JPG, PNG or WebP, up to 5 MB. Pages without a photo use the default. Save to apply.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
