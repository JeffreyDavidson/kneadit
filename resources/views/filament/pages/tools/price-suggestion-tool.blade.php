<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Recipe Selection -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-(--kn-ink)">Price Suggestion Tool</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="recipe" class="mb-1 block text-sm font-medium text-(--kn-ink-2)"
                        >Select Recipe with Cost Data</label>
                    <select
                        wire:model.live="selectedRecipeId"
                        id="recipe"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-(--kn-border) shadow-sm"
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
                        <p class="mt-1 text-sm text-(--kn-danger)">
                            No recipes with cost data found. Please calculate recipe costs first.
                        </p>
                    @endif
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
            <!-- Recipe Overview -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-xl font-bold text-(--kn-ink)">{{ $selectedRecipe->name }}</h3>

                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-(--kn-info-tint) p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-currency-dollar class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-(--kn-info)">Recipe Cost</p>
                                <p class="text-2xl font-bold text-(--kn-info)">@money($selectedRecipe->cost)</p>
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
                        <div class="rounded-lg bg-(--kn-success-tint) p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-tag class="h-8 w-8 text-(--kn-success)" stroke-width="2" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-(--kn-success)">Current Price</p>
                                    <p class="text-2xl font-bold text-(--kn-success)">
                                        @money($selectedRecipe->product->price)
                                    </p>
                                    <p class="text-xs text-(--kn-success)">{{ $selectedRecipe->product->name }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($this->getMarginAtCurrentPrice())
                            @php
                                $currentMarginData = $this->getMarginAtCurrentPrice();
                                [$marginTint, $marginText] = match ($currentMarginData['color']) {
                                    'green' => ['bg-(--kn-success-tint)', 'text-(--kn-success)'],
                                    'yellow' => ['bg-(--kn-warning-tint)', 'text-(--kn-warning)'],
                                    default => ['bg-(--kn-danger-tint)', 'text-(--kn-danger)'],
                                };
                            @endphp
                            <div class="{{ $marginTint }} rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-o-chart-bar class="w-8 h-8 {{ $marginText }}" stroke-width="2" />
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium {{ $marginText }}">Current Margin</p>
                                        <p class="text-2xl font-bold {{ $marginText }}">
                                            {{ number_format($currentMarginData['margin'], 1) }}%
                                        </p>
                                        <p class="text-xs {{ $marginText }}">
                                            @money($currentMarginData['profit'])
                                            profit
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="rounded-lg bg-(--kn-surface-sunken) p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-exclamation-triangle
                                        class="h-8 w-8 text-(--kn-muted)"
                                        stroke-width="2"
                                    />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-(--kn-muted)">No Product Linked</p>
                                    <p class="text-sm text-(--kn-muted)">
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
                    <div class="mb-6 rounded-lg bg-(--kn-surface-sunken) p-4">
                        <h4 class="mb-3 text-lg font-semibold text-(--kn-ink)">Price Analysis</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-(--kn-muted)">Price adjustment needed:</p>
                                <p class="text-xl font-bold {{ $priceDiff['direction'] == 'increase' ? 'text-(--kn-danger)' : 'text-(--kn-success)' }}">
                                    {{ $priceDiff['direction'] == 'increase' ? '+' : '' }}${{ number_format($priceDiff['amount'], 2) }}
                                </p>
                                <p class="text-sm text-(--kn-muted)">
                                    ({{ $priceDiff['direction'] == 'increase' ? '+' : '' }}{{ number_format($priceDiff['percentage'], 1) }}%)
                                </p>
                            </div>
                            <div>
                                @if (abs($priceDiff['amount']) > 0.50)
                                    <div class="p-3 {{ $priceDiff['direction'] == 'increase' ? 'bg-(--kn-danger-tint) border border-(--kn-danger) text-(--kn-danger)' : 'bg-(--kn-success-tint) border border-(--kn-success) text-(--kn-success)' }} rounded-md">
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
                                    <div class="rounded-md border border-(--kn-info) bg-(--kn-info-tint) p-3 text-(--kn-info)">
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
                        <h4 class="mb-4 text-lg font-semibold text-(--kn-ink)">Pricing at Different Margins</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-(--kn-border)">
                                <thead class="bg-(--kn-surface-sunken)">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Margin %
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Suggested Price
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                            Profit per Unit
                                        </th>
                                        @if ($selectedRecipe->product)
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                                Difference from Current
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-(--kn-border) bg-(--kn-surface)">
                                    @foreach ($marginComparisons as $comparison)
                                        <tr class="{{ $comparison['is_target'] ? 'bg-(--kn-info-tint)' : '' }}">
                                            <td class="px-4 py-3 text-sm font-medium {{ $comparison['is_target'] ? 'text-(--kn-info)' : 'text-(--kn-ink)' }}">
                                                {{ $comparison['margin'] }}%
                                                @if ($comparison['is_target'])
                                                    <span class="ml-2 inline-flex items-center rounded bg-(--kn-info-tint) px-2 py-0.5 text-xs font-medium text-(--kn-info)">
                                                        Target
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm {{ $comparison['is_target'] ? 'font-bold text-(--kn-info)' : 'text-(--kn-muted)' }}">
                                                @money($comparison['price'])
                                            </td>
                                            <td class="px-4 py-3 text-sm {{ $comparison['is_target'] ? 'font-bold text-(--kn-info)' : 'text-(--kn-muted)' }}">
                                                @money($comparison['price'] - $selectedRecipe->cost)
                                            </td>
                                            @if ($selectedRecipe->product)
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="{{ $comparison['difference'] > 0 ? 'text-(--kn-danger)' : 'text-(--kn-success)' }}">
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
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <div class="text-center">
                    <x-heroicon-o-currency-dollar class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                    <h3 class="mb-2 text-lg font-medium text-(--kn-ink)">No recipes with cost data</h3>
                    <p class="mb-4 text-(--kn-muted)">
                        To use the price suggestion tool, you need recipes with calculated costs.
                    </p>
                    <div class="text-sm text-(--kn-muted)">
                        <p>Go to <strong>Recipe Cost Calculator</strong> to calculate costs for your recipes first.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
