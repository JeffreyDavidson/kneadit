<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recipe Cost Calculator</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="recipe" class="block text-sm font-medium text-gray-700 mb-1">Select Recipe</label>
                    <select wire:model.live="selectedRecipeId" 
                            id="recipe"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Choose a recipe...</option>
                        @foreach($recipes as $recipe)
                            <option value="{{ $recipe->id }}">
                                {{ $recipe->name }}
                                @if($recipe->product)
                                    ({{ $recipe->product->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($selectedRecipe)
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

        @if($selectedRecipe)
            <!-- Recipe Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $selectedRecipe->name }}</h3>
                
                @if($selectedRecipe->product)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Product:</span>
                                <p class="text-gray-900">{{ $selectedRecipe->product->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Current Price:</span>
                                <p class="text-gray-900">${{ number_format($selectedRecipe->product->price, 2) }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Prep Time:</span>
                                <p class="text-gray-900">
                                    {{ $selectedRecipe->prep_time_minutes }} 
                                    {{ $selectedRecipe->prep_time_minutes == 1 ? 'minute' : 'minutes' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ingredients -->
                @if($this->getFormattedIngredients()->isNotEmpty())
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Ingredients & Costs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ingredient
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cost per Unit
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($this->getFormattedIngredients() as $ingredient)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $ingredient['name'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ number_format($ingredient['quantity'], 2) }} {{ $ingredient['unit'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                ${{ number_format($ingredient['cost_per_unit'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                ${{ number_format($ingredient['total_cost'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Cost Analysis -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total Recipe Cost</p>
                                <p class="text-2xl font-bold text-blue-900">${{ number_format($totalRecipeCost, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($selectedRecipe->product)
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-600">Current Margin</p>
                                    <p class="text-2xl font-bold {{ $currentMargin >= 50 ? 'text-green-900' : ($currentMargin >= 30 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($currentMargin, 1) }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Target Margin</p>
                                <p class="text-2xl font-bold text-purple-900">{{ number_format($targetMarginPercentage, 1) }}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-orange-600">Suggested Price</p>
                                <p class="text-2xl font-bold text-orange-900">${{ number_format($suggestedPrice, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Comparison -->
                @if($selectedRecipe->product)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Price Analysis</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Current selling price:</p>
                                <p class="text-lg font-bold text-gray-900">${{ number_format($selectedRecipe->product->price, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Price difference:</p>
                                @php
                                    $priceDiff = $suggestedPrice - $selectedRecipe->product->price;
                                @endphp
                                <p class="text-lg font-bold {{ $priceDiff > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $priceDiff > 0 ? '+' : '' }}${{ number_format($priceDiff, 2) }}
                                </p>
                            </div>
                        </div>
                        
                        @if(abs($priceDiff) > 0.50)
                            <div class="mt-3 p-3 {{ $priceDiff > 0 ? 'bg-red-100 border border-red-200 text-red-700' : 'bg-green-100 border border-green-200 text-green-700' }} rounded-md">
                                @if($priceDiff > 0)
                                    <p class="text-sm">
                                        💡 Consider increasing the price by ${{ number_format($priceDiff, 2) }} to achieve your target margin of {{ number_format($targetMarginPercentage, 1) }}%.
                                    </p>
                                @else
                                    <p class="text-sm">
                                        ✅ Your current price already exceeds the target margin. You could lower the price by ${{ number_format(abs($priceDiff), 2) }} and still maintain your target margin.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>