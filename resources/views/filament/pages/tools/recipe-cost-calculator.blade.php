<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recipe Cost Calculator</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="recipe" class="mb-1 block text-sm font-medium text-gray-700">Select Recipe</label>
                    <select
                        wire:model.live="selectedRecipeId"
                        id="recipe"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                    >
                        <option value="">Choose a recipe...</option>
                        @foreach ($recipes as $recipe)
                            <option value="{{ $recipe->id }}">
                                {{ $recipe->name }}
                                @if ($recipe->product)
                                    ({{ $recipe->product->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
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
            <!-- Recipe Details -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-xl font-bold text-gray-900">{{ $selectedRecipe->name }}</h3>

                @if ($selectedRecipe->product)
                    <div class="mb-4 rounded-lg bg-gray-50 p-4">
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                            <div>
                                <span class="font-medium text-gray-700">Product:</span>
                                <p class="text-gray-900">{{ $selectedRecipe->product->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Current Price:</span>
                                <p class="text-gray-900">@money($selectedRecipe->product->price)</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Prep Time:</span>
                                <p class="text-gray-900">
                                    {{ $selectedRecipe->prep_time_minutes }} {{ $selectedRecipe->prep_time_minutes == 1 ? 'minute' : 'minutes' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ingredients -->
                @if ($this->getFormattedIngredients()->isNotEmpty())
                    <div class="mb-6">
                        <h4 class="mb-3 text-lg font-semibold text-gray-900">Ingredients & Costs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Ingredient
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Quantity
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Cost per Unit
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                            Total Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($this->getFormattedIngredients() as $ingredient)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $ingredient['name'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ number_format($ingredient['quantity'], 2) }} {{ $ingredient['unit'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @money($ingredient['cost_per_unit'])
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                @money($ingredient['total_cost'])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Cost Analysis -->
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-blue-600" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total Recipe Cost</p>
                                <p class="text-2xl font-bold text-blue-900">@money($totalRecipeCost)</p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedRecipe->product)
                        <div class="rounded-lg bg-green-50 p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-arrow-trending-up class="h-8 w-8 text-green-600" stroke-width="2" />
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

                    <div class="rounded-lg bg-purple-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-calculator class="h-8 w-8 text-purple-600" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Target Margin</p>
                                <p class="text-2xl font-bold text-purple-900">
                                    {{ number_format($targetMarginPercentage, 1) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-orange-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-orange-600" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-orange-600">Suggested Price</p>
                                <p class="text-2xl font-bold text-orange-900">@money($suggestedPrice)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Comparison -->
                @if ($selectedRecipe->product)
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h4 class="mb-3 text-lg font-semibold text-gray-900">Price Analysis</h4>
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                            <div>
                                <p class="text-gray-600">Current selling price:</p>
                                <p class="text-lg font-bold text-gray-900">@money($selectedRecipe->product->price)</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Price difference:</p>
                                @php
                                    $priceDiff = $suggestedPrice - ($selectedRecipe->product->price?->dollars() ?? 0);
                                @endphp
                                <p class="text-lg font-bold {{ $priceDiff > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $priceDiff > 0 ? '+' : '' }}${{ number_format($priceDiff, 2) }}
                                </p>
                            </div>
                        </div>

                        @if (abs($priceDiff) > 0.50)
                            <div class="mt-3 p-3 {{ $priceDiff > 0 ? 'bg-red-100 border border-red-200 text-red-700' : 'bg-green-100 border border-green-200 text-green-700' }} rounded-md">
                                @if ($priceDiff > 0)
                                    <p class="flex items-start gap-2 text-sm">
                                        <x-filament::icon icon="heroicon-o-light-bulb" class="h-4 w-4 shrink-0" />
                                        <span
                                            >Consider increasing the price by
                                            @money($priceDiff)
                                            to achieve your target margin of {{ number_format($targetMarginPercentage, 1) }}%.</span>
                                    </p>
                                @else
                                    <p class="flex items-start gap-2 text-sm">
                                        <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 shrink-0" />
                                        <span
                                            >Your current price already exceeds the target margin. You could lower the
                                            price by
                                            @money(abs($priceDiff))
                                            and still maintain your target margin.</span>
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
