<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Input --}}
        <x-filament::section heading="Pricing Inputs">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Product</label>
                    <select wire:model.live="selectedProductId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="">— Select a product —</option>
                        @foreach($this->products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->category?->name ?? 'Uncategorized' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ingredient Cost ($)</label>
                    <input type="number" step="0.01" wire:model="ingredientCost" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prep Time (minutes)</label>
                    <input type="number" step="1" wire:model="prepTimeMinutes" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hourly Labor Rate ($)</label>
                    <input type="number" step="0.50" wire:model="hourlyLaborRate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overhead (%)</label>
                    <input type="number" step="1" min="0" max="100" wire:model="overheadPercentage" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Competitive Positioning</label>
                    <select wire:model="positioning" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="economy">Economy (0.85×)</option>
                        <option value="standard">Standard (1.0×)</option>
                        <option value="premium">Premium (1.25×)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Profit Margin: {{ $targetProfitMargin }}%</label>
                <input type="range" min="30" max="70" step="1" wire:model.live="targetProfitMargin" class="w-full">
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
        @if($result)
            <x-filament::section heading="Pricing Breakdown">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Cost Breakdown --}}
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Cost Breakdown</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Ingredients</span>
                                <span class="font-medium">${{ number_format($result['ingredient_cost'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Labor</span>
                                <span class="font-medium">${{ number_format($result['labor_cost'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Overhead ({{ $overheadPercentage }}%)</span>
                                <span class="font-medium">${{ number_format($result['overhead'], 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 border-gray-300 dark:border-gray-600">
                                <span class="font-semibold">Total Cost</span>
                                <span class="font-bold">${{ number_format($result['total_cost'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Recommended Price --}}
                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Recommended Price</h3>
                        <div class="text-center">
                            <p class="text-4xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($result['recommended_price'], 2) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Profit: ${{ number_format($result['profit_per_unit'], 2) }} ({{ $result['actual_margin'] }}% margin)</p>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            <div class="flex justify-between">
                                <span>Min viable</span>
                                <span>${{ number_format($result['min_price'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Premium</span>
                                <span>${{ number_format($result['max_price'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Comparison & Bulk --}}
                    <div class="space-y-3">
                        @if($result['current_price'] !== null)
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Current Price Comparison</h3>
                            <div class="text-center">
                                <p class="text-2xl font-bold">${{ number_format($result['current_price'], 2) }}</p>
                                @php
                                    $diff = $result['recommended_price'] - $result['current_price'];
                                @endphp
                                <p class="text-sm mt-1 {{ $diff > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                    @if($diff > 0)
                                        Consider raising by ${{ number_format(abs($diff), 2) }}
                                    @elseif($diff < 0)
                                        Currently ${{ number_format(abs($diff), 2) }} above suggested
                                    @else
                                        Right on target!
                                    @endif
                                </p>
                            </div>
                        @endif

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-4">Bulk Pricing</h3>
                        <div class="space-y-2 text-sm">
                            @foreach($result['bulk'] as $bulk)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $bulk['label'] }}</span>
                                    <span class="font-medium">${{ number_format($bulk['unit_price'], 2) }}/ea (${{ number_format($bulk['total'], 2) }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
