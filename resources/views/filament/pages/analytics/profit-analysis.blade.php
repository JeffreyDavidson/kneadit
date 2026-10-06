<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Overall Statistics -->
        @php $stats = $this->getOverallStats(); @endphp
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="rounded-lg bg-(--kn-info-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-tag class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-info)">Total Products</p>
                        <p class="text-2xl font-bold text-(--kn-info)">{{ $stats['total_products'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-(--kn-success-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="h-8 w-8 text-(--kn-success)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-success)">With Cost Data</p>
                        <p class="text-2xl font-bold text-(--kn-success)">{{ $stats['products_with_costs'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-purple-50 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-arrow-trending-up class="h-8 w-8 text-purple-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Avg Margin</p>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ $stats['average_margin'] ? $stats['average_margin'] . '%' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-(--kn-warning-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-information-circle class="h-8 w-8 text-(--kn-warning)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-warning)">Missing Cost Data</p>
                        <p class="text-2xl font-bold text-(--kn-warning)">{{ $stats['products_missing_costs'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-(--kn-surface-sunken) p-4">
                <div class="text-center">
                    <p class="mb-2 text-xs font-medium text-(--kn-muted)">Margin Breakdown</p>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <div class="text-center">
                            <div class="font-bold text-(--kn-success)">{{ $stats['margin_breakdown']['high'] }}</div>
                            <div class="text-(--kn-success)">High</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-(--kn-warning)">{{ $stats['margin_breakdown']['medium'] }}</div>
                            <div class="text-(--kn-warning)">Med</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-(--kn-danger)">{{ $stats['margin_breakdown']['low'] }}</div>
                            <div class="text-(--kn-danger)">Low</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Potential -->
        @php $potential = $this->getTotalRevenuePotential(); @endphp
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Revenue Potential Analysis</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-(--kn-info-tint) p-4 text-center">
                    <div class="text-2xl font-bold text-(--kn-info)">@money($potential['total_revenue_potential'])</div>
                    <div class="text-sm text-(--kn-info)">Potential Revenue</div>
                </div>
                <div class="rounded-lg bg-(--kn-danger-tint) p-4 text-center">
                    <div class="text-2xl font-bold text-(--kn-danger)">@money($potential['total_costs'])</div>
                    <div class="text-sm text-(--kn-danger)">Total Costs</div>
                </div>
                <div class="rounded-lg bg-(--kn-success-tint) p-4 text-center">
                    <div class="text-2xl font-bold text-(--kn-success)">
                        @money($potential['total_profit_potential'])
                    </div>
                    <div class="text-sm text-(--kn-success)">Potential Profit</div>
                </div>
                <div class="rounded-lg bg-purple-50 p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $potential['overall_margin'] }}%</div>
                    <div class="text-sm text-purple-700">Overall Margin</div>
                </div>
            </div>
        </div>

        <!-- Product Analysis Table -->
        <div class="overflow-hidden rounded-lg bg-(--kn-surface) shadow">
            <div class="border-b border-(--kn-border) px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-(--kn-ink)">Product Profit Analysis</h3>
                    <div>
                        <label for="sort_by" class="mb-1 block text-xs font-medium text-(--kn-ink-2)">Sort by</label>
                        <select
                            wire:model.live="sortBy"
                            id="sort_by"
                            class="focus:border-primary-500 focus:ring-primary-500 rounded-md border-(--kn-border) text-sm shadow-sm"
                        >
                            <option value="margin_desc">Margin (High to Low)</option>
                            <option value="margin_asc">Margin (Low to High)</option>
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="price_desc">Price (High to Low)</option>
                            <option value="price_asc">Price (Low to High)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-(--kn-border)">
                    <thead class="bg-(--kn-surface-sunken)">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Product Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Cost
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Margin %
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Margin $
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--kn-border) bg-(--kn-surface)">
                        @foreach ($this->getProductAnalysis() as $product)
                            @php
                                $marginText = match ($product['color_class']) {
                                    'green' => 'text-(--kn-success)',
                                    'yellow' => 'text-(--kn-warning)',
                                    default => 'text-(--kn-danger)',
                                };
                                $marginChip = match ($product['color_class']) {
                                    'green' => 'bg-(--kn-success-tint) text-(--kn-success)',
                                    'yellow' => 'bg-(--kn-warning-tint) text-(--kn-warning)',
                                    default => 'bg-(--kn-danger-tint) text-(--kn-danger)',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-(--kn-ink)">
                                    {{ $product['name'] }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-(--kn-muted)">
                                    @if ($product['price'])
                                        @money($product['price'])
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-(--kn-muted)">
                                    @if ($product['cost'])
                                        @money($product['cost'])
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @if ($product['margin_percentage'] !== null)
                                        <span class="font-medium {{ $marginText }}">
                                            {{ $product['margin_percentage'] }}%
                                        </span>
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @if ($product['margin_amount'] !== null)
                                        <span class="font-medium {{ $marginText }}">
                                            @money($product['margin_amount'])
                                        </span>
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @if ($product['has_cost_data'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $marginChip }}">
                                            @if ($product['color_class'] === 'green')
                                                High Margin
                                            @elseif ($product['color_class'] === 'yellow')
                                                Medium Margin
                                            @else
                                                Low Margin
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-(--kn-surface-sunken) px-2.5 py-0.5 text-xs font-medium text-(--kn-ink-2)">
                                            Missing Cost Data
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Top Profitable Products -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Top Profitable Products</h3>

                @php $topProducts = $this->getTopProfitableProducts(); @endphp
                @if ($topProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($topProducts as $product)
                            <div class="flex items-center justify-between rounded-lg bg-(--kn-success-tint) p-3">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-(--kn-ink)">{{ $product['name'] }}</p>
                                    <p class="text-xs text-(--kn-muted)">{{ $product['margin_percentage'] }}% margin</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-(--kn-success)">
                                        @money($product['margin_amount'])
                                    </p>
                                    <p class="text-xs text-(--kn-muted)">profit per unit</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-arrow-trending-up
                            class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)"
                            stroke-width="2"
                        />
                        <p class="text-(--kn-muted)">No profitable products with cost data</p>
                    </div>
                @endif
            </div>

            <!-- Lowest Margin Products -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Products Needing Attention</h3>

                @php $lowProducts = $this->getLowestMarginProducts(); @endphp
                @if ($lowProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($lowProducts as $product)
                            <div class="flex items-center justify-between rounded-lg bg-(--kn-danger-tint) p-3">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-(--kn-ink)">{{ $product['name'] }}</p>
                                    <p class="text-xs text-(--kn-muted)">Needs price review</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-(--kn-danger)">
                                        {{ $product['margin_percentage'] }}%
                                    </p>
                                    <p class="text-xs text-(--kn-muted)">margin</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-check-circle class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                        <p class="text-(--kn-muted)">No products with margin data</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Missing Cost Data -->
        @php $missingCost = $this->getMissingCostProducts(); @endphp
        @if ($missingCost->isNotEmpty())
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Products Missing Cost Data</h3>
                <div class="mb-4 rounded-lg bg-(--kn-warning-tint) p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-(--kn-warning)" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-(--kn-warning)">
                                Add cost data to improve profit analysis
                            </h4>
                            <p class="mt-1 text-sm text-(--kn-warning)">
                                The following products don't have cost data. Add costs to their product record or create
                                recipes with cost calculations.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($missingCost as $product)
                        <div class="flex items-center justify-between rounded-lg border bg-(--kn-surface-sunken) p-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-(--kn-ink)">{{ $product['name'] }}</p>
                                @if ($product['price'])
                                    <p class="text-xs text-(--kn-muted)">
                                        Price:
                                        @money($product['price'])
                                    </p>
                                @else
                                    <p class="text-xs text-(--kn-danger)">No price set</p>
                                @endif
                            </div>
                            <div class="ml-2">
                                <span class="inline-flex items-center rounded bg-(--kn-warning-tint) px-2 py-0.5 text-xs font-medium text-(--kn-warning)">
                                    No Cost
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Legend -->
        <div class="rounded-lg bg-(--kn-surface-sunken) p-6">
            <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Margin Color Guide</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="flex items-center rounded-lg bg-(--kn-success-tint) p-3">
                    <div class="mr-3 h-4 w-4 rounded bg-(--kn-success)"></div>
                    <div>
                        <p class="text-sm font-medium text-(--kn-success)">High Margin</p>
                        <p class="text-xs text-(--kn-success)">50% or higher</p>
                    </div>
                </div>
                <div class="flex items-center rounded-lg bg-(--kn-warning-tint) p-3">
                    <div class="mr-3 h-4 w-4 rounded bg-(--kn-warning)"></div>
                    <div>
                        <p class="text-sm font-medium text-(--kn-warning)">Medium Margin</p>
                        <p class="text-xs text-(--kn-warning)">30% - 49%</p>
                    </div>
                </div>
                <div class="flex items-center rounded-lg bg-(--kn-danger-tint) p-3">
                    <div class="mr-3 h-4 w-4 rounded bg-(--kn-danger)"></div>
                    <div>
                        <p class="text-sm font-medium text-(--kn-danger)">Low Margin</p>
                        <p class="text-xs text-(--kn-danger)">Under 30%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
