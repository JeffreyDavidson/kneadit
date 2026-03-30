<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Input Form --}}
        <x-filament::section heading="Product Details">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Product</label>
                    <select wire:model.live="selectedProductId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="">— Manual entry —</option>
                        @foreach ($this->products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->category?->name ?? 'Uncategorized' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Or Type Product Name</label>
                    <input type="text" wire:model="manualProductName" placeholder="e.g. Chocolate Croissant" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm" @if ($selectedProductId) disabled @endif>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tone</label>
                    <select wire:model="tone" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="professional">Professional</option>
                        <option value="casual">Casual</option>
                        <option value="playful">Playful</option>
                        <option value="luxurious">Luxurious</option>
                        <option value="homey">Homey</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Length</label>
                    <select wire:model="length" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
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
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-4">
                                <p class="text-gray-800 dark:text-gray-200 flex-1">{{ $description }}</p>
                                <div class="flex gap-2 shrink-0">
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
