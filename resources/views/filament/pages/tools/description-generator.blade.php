<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Input Form --}}
        <x-filament::section heading="Product Details">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Product</label>
                    <select
                        wire:model.live="selectedProductId"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">— Manual entry —</option>
                        @foreach ($this->products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ $product->category?->name ?? 'Uncategorized' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Or Type Product Name</label>
                    <input
                        type="text"
                        wire:model="manualProductName"
                        placeholder="e.g. Chocolate Croissant"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @if ($selectedProductId) disabled @endif
                    />
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tone</label>
                    <select
                        wire:model="tone"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="professional">Professional</option>
                        <option value="casual">Casual</option>
                        <option value="playful">Playful</option>
                        <option value="luxurious">Luxurious</option>
                        <option value="homey">Homey</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Length</label>
                    <select
                        wire:model="length"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="short">Short (1 sentence)</option>
                        <option value="medium">Medium (2-3 sentences)</option>
                        <option value="long">Long (paragraph)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <x-filament::button wire:click="generate" icon="heroicon-o-sparkles">
                    Generate Descriptions
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Results --}}
        @if (count($descriptions) > 0)
            <x-filament::section heading="Generated Descriptions">
                <div class="space-y-4">
                    @foreach ($descriptions as $index => $description)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-start justify-between gap-4">
                                <p class="flex-1 text-gray-800 dark:text-gray-200">{{ $description }}</p>
                                <div class="flex shrink-0 gap-2">
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        icon="heroicon-o-clipboard"
                                        x-on:click="navigator.clipboard.writeText(@js($description)); $tooltip('Copied!')"
                                    >
                                        Copy
                                    </x-filament::button>

                                    @if ($selectedProductId)
                                        <x-filament::button
                                            size="sm"
                                            color="success"
                                            icon="heroicon-o-check"
                                            wire:click="applyToProduct({{ $index }})"
                                        >
                                            Apply
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
