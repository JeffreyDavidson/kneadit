<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Price Suggestion Tool</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="recipe" class="mb-1 block text-sm font-medium text-gray-700"
                        >Select Recipe with Cost Data</label>
                    <select
                        wire:model.live="selectedRecipeId"
                        id="recipe"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                    >
                        <option value="">Choose a recipe...</option>
                        @foreach ($recipes as $recipe)
                            <option value="{{ $recipe->id }}">
                                {{ $recipe->name }} - Cost:
                                @money($recipe->cost)
                                @if ($recipe->product)
                                    | Product: {{ $recipe->product->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if ($recipes->isEmpty())
                        <p class="mt-1 text-sm text-red-600">
                            No recipes with cost data found. Please calculate recipe costs first.
                        </p>
                    @endif
                </div>

                @if ($selectedRecipe)
                    <div>
                        <label for="target_margin" class="mb-1 block text-sm font-medium text-gray-700">
                            Target Margin %
                        </label>
                        <input
                            type="number"
                            wire:model.live="targetMarginPercentage"
                            id="target_margin"
                            step="0.1"
                            min="0"
                            max="100"
                            class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </div>
                @endif
            </div>
        </div>

        @if ($selectedRecipe)
            <!-- Recipe Overview -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-xl font-bold text-gray-900">{{ $selectedRecipe->name }}</h3>

                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-blue-600" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Recipe Cost</p>
                                <p class="text-2xl font-bold text-blue-900">@money($selectedRecipe->cost)</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-purple-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-arrow-trending-up class="h-8 w-8 text-purple-600" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Suggested Price</p>
                                <p class="text-2xl font-bold text-purple-900">@money($this->getSuggestedPrice())</p>
                                <p class="text-xs text-purple-600">
                                    at {{ number_format($targetMarginPercentage, 1) }}% margin
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedRecipe->product)
                        <div class="rounded-lg bg-green-50 p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-tag class="h-8 w-8 text-green-600" stroke-width="2" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-600">Current Price</p>
                                    <p class="text-2xl font-bold text-green-900">
                                        @money($selectedRecipe->product->price)
                                    </p>
                                    <p class="text-xs text-green-600">{{ $selectedRecipe->product->name }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($this->getMarginAtCurrentPrice())
                            @php $currentMarginData = $this->getMarginAtCurrentPrice(); @endphp
                            <div class="bg-{{ $currentMarginData['color'] }}-50 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-o-chart-bar
                                            class="w-8 h-8 text-{{ $currentMarginData['color'] }}-600"
                                            stroke-width="2"
                                        />
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-{{ $currentMarginData['color'] }}-600">
                                            Current Margin
                                        </p>
                                        <p class="text-2xl font-bold text-{{ $currentMarginData['color'] }}-900">
                                            {{ number_format($currentMarginData['margin'], 1) }}%
                                        </p>
                                        <p class="text-xs text-{{ $currentMarginData['color'] }}-600">
                                            @money($currentMarginData['profit'])
                                            profit
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-gray-400" stroke-width="2" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-600">No Product Linked</p>
                                    <p class="text-sm text-gray-500">
                                        Link this recipe to a product to see current pricing
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Price Difference Analysis -->
                @if ($selectedRecipe->product && $this->getPriceDifference())
                    @php $priceDiff = $this->getPriceDifference(); @endphp
                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-lg font-semibold text-gray-900">Price Analysis</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600">Price adjustment needed:</p>
                                <p class="text-xl font-bold {{ $priceDiff['direction'] == 'increase' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $priceDiff['direction'] == 'increase' ? '+' : '' }}${{ number_format($priceDiff['amount'], 2) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    ({{ $priceDiff['direction'] == 'increase' ? '+' : '' }}{{ number_format($priceDiff['percentage'], 1) }}%)
                                </p>
                            </div>
                            <div>
                                @if (abs($priceDiff['amount']) > 0.50)
                                    <div class="p-3 {{ $priceDiff['direction'] == 'increase' ? 'bg-red-100 border border-red-200 text-red-700' : 'bg-green-100 border border-green-200 text-green-700' }} rounded-md">
                                        @if ($priceDiff['direction'] == 'increase')
                                            <p class="flex items-start gap-2 text-sm">
                                                <x-filament::icon
                                                    icon="heroicon-o-light-bulb"
                                                    class="h-4 w-4 shrink-0"
                                                />
                                                <span>Consider increasing the price to achieve your target margin of {{ number_format($targetMarginPercentage, 1) }}%.</span>
                                            </p>
                                        @else
                                            <p class="flex items-start gap-2 text-sm">
                                                <x-filament::icon
                                                    icon="heroicon-o-check-circle"
                                                    class="h-4 w-4 shrink-0"
                                                />
                                                <span
                                                    >Your current price already exceeds the target margin. You could
                                                    lower the price and still maintain profitability.</span>
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="rounded-md border border-blue-200 bg-blue-100 p-3 text-blue-700">
                                        <p class="flex items-start gap-2 text-sm">
                                            <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 shrink-0" />
                                            <span
                                                >Your current price is very close to the suggested price for your target
                                                margin.</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Margin Comparison Table -->
                @if ($marginComparisons->isNotEmpty())
                    <div>
                        <h4 class="mb-4 text-lg font-semibold text-gray-900">Pricing at Different Margins</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Margin %
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Suggested Price
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Profit per Unit
                                        </th>
                                        @if ($selectedRecipe->product)
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                                Difference from Current
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($marginComparisons as $comparison)
                                        <tr class="{{ $comparison['is_target'] ? 'bg-blue-50' : '' }}">
                                            <td class="px-4 py-3 text-sm font-medium {{ $comparison['is_target'] ? 'text-blue-900' : 'text-gray-900' }}">
                                                {{ $comparison['margin'] }}%
                                                @if ($comparison['is_target'])
                                                    <span class="ml-2 inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                        Target
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm {{ $comparison['is_target'] ? 'font-bold text-blue-900' : 'text-gray-600' }}">
                                                @money($comparison['price'])
                                            </td>
                                            <td class="px-4 py-3 text-sm {{ $comparison['is_target'] ? 'font-bold text-blue-900' : 'text-gray-600' }}">
                                                @money($comparison['price'] - $selectedRecipe->cost)
                                            </td>
                                            @if ($selectedRecipe->product)
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="{{ $comparison['difference'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $comparison['difference'] > 0 ? '+' : '' }}${{ number_format($comparison['difference'], 2) }} ({{ $comparison['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['difference_percentage'], 1) }}%)
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if ($recipes->isEmpty())
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="text-center">
                    <x-heroicon-o-currency-dollar class="mx-auto mb-4 h-12 w-12 text-gray-400" stroke-width="2" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">No recipes with cost data</h3>
                    <p class="mb-4 text-gray-500">
                        To use the price suggestion tool, you need recipes with calculated costs.
                    </p>
                    <div class="text-sm text-gray-600">
                        <p>Go to <strong>Recipe Cost Calculator</strong> to calculate costs for your recipes first.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
