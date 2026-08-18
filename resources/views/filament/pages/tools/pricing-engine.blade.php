<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Input --}}
        <x-filament::section heading="Pricing Inputs">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Product</label>
                    <select
                        wire:model.live="selectedProductId"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">— Select a product —</option>
                        @foreach ($this->products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ $product->category?->name ?? 'Uncategorized' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ingredient Cost ($)</label>
                    <input
                        type="number"
                        step="0.01"
                        wire:model="ingredientCost"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Prep Time (minutes)</label>
                    <input
                        type="number"
                        step="1"
                        wire:model="prepTimeMinutes"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Hourly Labor Rate ($)</label>
                    <input
                        type="number"
                        step="0.50"
                        wire:model="hourlyLaborRate"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Overhead (%)</label>
                    <input
                        type="number"
                        step="1"
                        min="0"
                        max="100"
                        wire:model="overheadPercentage"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Competitive Positioning</label>
                    <select
                        wire:model="positioning"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="economy">Economy (0.85×)</option>
                        <option value="standard">Standard (1.0×)</option>
                        <option value="premium">Premium (1.25×)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Target Profit Margin: {{ $targetProfitMargin }}%</label>
                <input type="range" min="30" max="70" step="1" wire:model.live="targetProfitMargin" class="w-full" />
                <div class="flex justify-between text-xs text-gray-500">
                    <span>30%</span>
                    <span>50%</span>
                    <span>70%</span>
                </div>
            </div>

            <div class="mt-4">
                <x-filament::button wire:click="calculate" icon="heroicon-o-calculator">
                    Calculate Price
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Results --}}
        @if ($result)
            <x-filament::section heading="Pricing Breakdown">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    {{-- Cost Breakdown --}}
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Cost Breakdown</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Ingredients</span>
                                <span class="font-medium">@money($result->ingredientCost)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Labor</span>
                                <span class="font-medium">@money($result->laborCost)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Overhead ({{ $overheadPercentage }}%)</span>
                                <span class="font-medium">@money($result->overhead)</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-300 pt-2 dark:border-gray-600">
                                <span class="font-semibold">Total Cost</span>
                                <span class="font-bold">@money($result->totalCost)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Recommended Price --}}
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Recommended Price</h3>
                        <div class="text-center">
                            <p class="text-primary-600 dark:text-primary-400 text-4xl font-bold">
                                @money($result->recommendedPrice)
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                Profit:
                                @money($result->profitPerUnit)
                                ({{ $result->actualMarginPercent }}% margin)
                            </p>
                        </div>
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>Min viable</span>
                                <span>@money($result->minPrice)</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Premium</span>
                                <span>@money($result->maxPrice)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Comparison & Bulk --}}
                    <div class="space-y-3">
                        @if ($result->currentPrice !== null)
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                Current Price Comparison
                            </h3>
                            <div class="text-center">
                                <p class="text-2xl font-bold">@money($result->currentPrice)</p>
                                @php
                                    $diff = $result->recommendedPrice - $result->currentPrice;
                                @endphp
                                <p class="text-sm mt-1 {{ $diff > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                    @if ($diff > 0)
                                        Consider raising by
                                        @money(abs($diff))
                                    @elseif ($diff < 0)
                                        Currently
                                        @money(abs($diff))
                                        above suggested
                                    @else
                                        Right on target!
                                    @endif
                                </p>
                            </div>
                        @endif

                        <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Bulk Pricing</h3>
                        <div class="space-y-2 text-sm">
                            @foreach ($result->bulkTiers as $bulk)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $bulk['label'] }}</span>
                                    <span class="font-medium">
                                        @money($bulk['unit_price'])
                                        /ea (
                                        @money($bulk['total'])
                                        )</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
