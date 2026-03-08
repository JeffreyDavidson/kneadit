<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Choose which widgets appear on your dashboard and arrange them in your preferred order.
                </p>
            </div>
            <div class="flex gap-2">
                <x-filament::button color="gray" wire:click="resetDefaults" icon="heroicon-o-arrow-path">
                    Reset to Defaults
                </x-filament::button>
                <x-filament::button wire:click="save" icon="heroicon-o-check">
                    Save Layout
                </x-filament::button>
            </div>
        </div>

        {{-- Widget Cards --}}
        <div class="space-y-2">
            @foreach($widgets as $index => $widget)
                <div
                    class="flex items-center gap-4 rounded-xl border p-4 transition-all duration-200
                        {{ $widget['visible']
                            ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700'
                            : 'bg-gray-50 border-gray-100 opacity-60 dark:bg-gray-900 dark:border-gray-800' }}"
                >
                    {{-- Order arrows --}}
                    <div class="flex flex-col gap-0.5">
                        <button
                            wire:click="moveUp({{ $index }})"
                            @class(['text-gray-400 hover:text-primary-500 transition-colors', 'invisible' => $index === 0])
                        >
                            <x-heroicon-s-chevron-up class="w-5 h-5" />
                        </button>
                        <button
                            wire:click="moveDown({{ $index }})"
                            @class(['text-gray-400 hover:text-primary-500 transition-colors', 'invisible' => $index === count($widgets) - 1])
                        >
                            <x-heroicon-s-chevron-down class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Icon --}}
                    <div class="text-2xl flex-shrink-0 w-10 text-center">
                        {{ $widget['icon'] }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $widget['name'] }}
                            </h3>
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                #{{ $widget['order'] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ $widget['description'] }}
                        </p>
                    </div>

                    {{-- Toggle --}}
                    <div class="flex-shrink-0">
                        <button
                            wire:click="toggleWidget({{ $index }})"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200
                                {{ $widget['visible'] ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200
                                    {{ $widget['visible'] ? 'translate-x-6' : 'translate-x-1' }}"
                            ></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom save --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-sm text-gray-500 hover:text-primary-500 flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Back to Dashboard
            </a>
            <x-filament::button wire:click="save" icon="heroicon-o-check">
                Save Layout
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
