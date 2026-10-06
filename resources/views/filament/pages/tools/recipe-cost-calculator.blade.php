<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-(--kn-ink)">Recipe Cost Calculator</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="recipe" class="mb-1 block text-sm font-medium text-(--kn-ink-2)">Select Recipe</label>
                    <select
                        wire:model.live="selectedRecipeId"
                        id="recipe"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-(--kn-border) shadow-sm"
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
                        <label for="target_margin" class="mb-1 block text-sm font-medium text-(--kn-ink-2)">
                            Target Margin %
                        </label>
                        <input
                            type="number"
                            wire:model.live="targetMarginPercentage"
                            id="target_margin"
                            step="0.1"
                            min="0"
                            max="100"
                            class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-(--kn-border) shadow-sm"
                        />
                    </div>
                @endif
            </div>
        </div>

        @if ($selectedRecipe)
            <!-- Recipe Details -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-xl font-bold text-(--kn-ink)">{{ $selectedRecipe->name }}</h3>

                @if ($selectedRecipe->product)
                    <div class="mb-4 rounded-lg bg-(--kn-surface-sunken) p-4">
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                            <div>
                                <span class="font-medium text-(--kn-ink-2)">Product:</span>
                                <p class="text-(--kn-ink)">{{ $selectedRecipe->product->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-(--kn-ink-2)">Current Price:</span>
                                <p class="text-(--kn-ink)">@money($selectedRecipe->product->price)</p>
                            </div>
                            <div>
                                <span class="font-medium text-(--kn-ink-2)">Prep Time:</span>
                                <p class="text-(--kn-ink)">
                                    {{ $selectedRecipe->prep_time_minutes }} {{ $selectedRecipe->prep_time_minutes == 1 ? 'minute' : 'minutes' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ingredients -->
                @if ($this->getFormattedIngredients()->isNotEmpty())
                    <div class="mb-6">
                        <h4 class="mb-3 text-lg font-semibold text-(--kn-ink)">Ingredients & Costs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-(--kn-border)">
                                <thead class="bg-(--kn-surface-sunken)">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Ingredient
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Quantity
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Cost per Unit
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Total Cost
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-(--kn-border) bg-(--kn-surface)">
                                    @foreach ($this->getFormattedIngredients() as $ingredient)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-(--kn-ink)">
                                                {{ $ingredient['name'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-(--kn-muted)">
                                                {{ number_format($ingredient['quantity'], 2) }} {{ $ingredient['unit'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-(--kn-muted)">
                                                @money($ingredient['cost_per_unit'])
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-(--kn-ink)">
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
                    <div class="rounded-lg bg-(--kn-info-tint) p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-(--kn-info)">Total Recipe Cost</p>
                                <p class="text-2xl font-bold text-(--kn-info)">@money($totalRecipeCost)</p>
                            </div>
                        </div>
                    </div>

                    @if ($selectedRecipe->product)
                        <div class="rounded-lg bg-(--kn-success-tint) p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-arrow-trending-up
                                        class="h-8 w-8 text-(--kn-success)"
                                        stroke-width="2"
                                    />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-(--kn-success)">Current Margin</p>
                                    <p class="text-2xl font-bold {{ $currentMargin >= 50 ? 'text-(--kn-success)' : ($currentMargin >= 30 ? 'text-(--kn-warning)' : 'text-(--kn-danger)') }}">
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

                    <div class="rounded-lg bg-(--kn-warning-tint) p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-(--kn-warning)" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-(--kn-warning)">Suggested Price</p>
                                <p class="text-2xl font-bold text-(--kn-warning)">@money($suggestedPrice)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Comparison -->
                @if ($selectedRecipe->product)
                    <div class="rounded-lg bg-(--kn-surface-sunken) p-4">
                        <h4 class="mb-3 text-lg font-semibold text-(--kn-ink)">Price Analysis</h4>
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                            <div>
                                <p class="text-(--kn-muted)">Current selling price:</p>
                                <p class="text-lg font-bold text-(--kn-ink)">@money($selectedRecipe->product->price)</p>
                            </div>
                            <div>
                                <p class="text-(--kn-muted)">Price difference:</p>
                                @php
                                    $priceDiff = $suggestedPrice - ($selectedRecipe->product->price?->dollars() ?? 0);
                                @endphp
                                <p class="text-lg font-bold {{ $priceDiff > 0 ? 'text-(--kn-danger)' : 'text-(--kn-success)' }}">
                                    {{ $priceDiff > 0 ? '+' : '' }}${{ number_format($priceDiff, 2) }}
                                </p>
                            </div>
                        </div>

                        @if (abs($priceDiff) > 0.50)
                            <div class="mt-3 p-3 {{ $priceDiff > 0 ? 'bg-(--kn-danger-tint) border border-(--kn-danger) text-(--kn-danger)' : 'bg-(--kn-success-tint) border border-(--kn-success) text-(--kn-success)' }} rounded-md">
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
