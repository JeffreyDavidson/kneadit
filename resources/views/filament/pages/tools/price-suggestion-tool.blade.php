<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Price Suggestion Tool</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="recipe" class="block text-sm font-medium text-gray-700 mb-1">Select Recipe with Cost Data</label>
                    <select wire:model.live="selectedRecipeId"
                            id="recipe"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Choose a recipe...</option>
                        @foreach ($recipes as $recipe)
                            <option value="{{ $recipe->id }}">
                                {{ $recipe->name }} - Cost: @money($recipe->cost)
                                @if ($recipe->product)
                                    | Product: {{ $recipe->product->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if ($recipes->isEmpty())
                        <p class="text-sm text-red-600 mt-1">No recipes with cost data found. Please calculate recipe costs first.</p>
                    @endif
                </div>

                @if ($selectedRecipe)
                    <div>
                        <label for="target_margin" class="block text-sm font-medium text-gray-700 mb-1">
                            Target Margin %
                        </label>
                        <input type="number"
                               wire:model.live="targetMarginPercentage"
                               id="target_margin"
                               step="0.1"
                               min="0"
                               max="100"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                    </div>
                @endif
            </div>
        </div>

        @if ($selectedRecipe)
            <!-- Recipe Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $selectedRecipe->name }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Recipe Cost</p>
                                <p class="text-2xl font-bold text-blue-900">@money($selectedRecipe->cost)</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Suggested Price</p>
                                <p class="text-2xl font-bold text-purple-900">@money($this->getSuggestedPrice())</p>
                                <p class="text-xs text-purple-600">at {{ number_format($targetMarginPercentage, 1) }}% margin</p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedRecipe->product)
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-600">Current Price</p>
                                    <p class="text-2xl font-bold text-green-900">@money($selectedRecipe->product->price)</p>
                                    <p class="text-xs text-green-600">{{ $selectedRecipe->product->name }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($this->getMarginAtCurrentPrice())
                            @php $currentMarginData = $this->getMarginAtCurrentPrice(); @endphp
                            <div class="bg-{{ $currentMarginData['color'] }}-50 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="w-8 h-8 text-{{ $currentMarginData['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-{{ $currentMarginData['color'] }}-600">Current Margin</p>
                                        <p class="text-2xl font-bold text-{{ $currentMarginData['color'] }}-900">{{ number_format($currentMarginData['margin'], 1) }}%</p>
                                        <p class="text-xs text-{{ $currentMarginData['color'] }}-600">@money($currentMarginData['profit']) profit</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-600">No Product Linked</p>
                                    <p class="text-sm text-gray-500">Link this recipe to a product to see current pricing</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Price Difference Analysis -->
                @if ($selectedRecipe->product && $this->getPriceDifference())
                    @php $priceDiff = $this->getPriceDifference(); @endphp
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Price Analysis</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                            <p class="text-sm">
                                                💡 Consider increasing the price to achieve your target margin of {{ number_format($targetMarginPercentage, 1) }}%.
                                            </p>
                                        @else
                                            <p class="text-sm">
                                                ✅ Your current price already exceeds the target margin. You could lower the price and still maintain profitability.
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="p-3 bg-blue-100 border border-blue-200 text-blue-700 rounded-md">
                                        <p class="text-sm">
                                            ✓ Your current price is very close to the suggested price for your target margin.
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
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Pricing at Different Margins</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Margin %
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Suggested Price
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Profit per Unit
                                        </th>
                                        @if ($selectedRecipe->product)
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Difference from Current
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($marginComparisons as $comparison)
                                        <tr class="{{ $comparison['is_target'] ? 'bg-blue-50' : '' }}">
                                            <td class="px-4 py-3 text-sm font-medium {{ $comparison['is_target'] ? 'text-blue-900' : 'text-gray-900' }}">
                                                {{ $comparison['margin'] }}%
                                                @if ($comparison['is_target'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 ml-2">
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
                                                        {{ $comparison['difference'] > 0 ? '+' : '' }}${{ number_format($comparison['difference'], 2) }}
                                                        ({{ $comparison['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['difference_percentage'], 1) }}%)
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
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No recipes with cost data</h3>
                    <p class="text-gray-500 mb-4">To use the price suggestion tool, you need recipes with calculated costs.</p>
                    <div class="text-sm text-gray-600">
                        <p>Go to <strong>Recipe Cost Calculator</strong> to calculate costs for your recipes first.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>