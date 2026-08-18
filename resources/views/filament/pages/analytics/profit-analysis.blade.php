<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Overall Statistics -->
        @php $stats = $this->getOverallStats(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-tag class="w-8 h-8 text-blue-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Total Products</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $stats['total_products'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">With Cost Data</p>
                        <p class="text-2xl font-bold text-green-900">{{ $stats['products_with_costs'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-arrow-trending-up class="w-8 h-8 text-purple-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Avg Margin</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $stats['average_margin'] ? $stats['average_margin'] . '%' : '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-information-circle class="w-8 h-8 text-orange-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-600">Missing Cost Data</p>
                        <p class="text-2xl font-bold text-orange-900">{{ $stats['products_missing_costs'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-center">
                    <p class="text-xs font-medium text-gray-600 mb-2">Margin Breakdown</p>
                    <div class="grid grid-cols-3 gap-1 text-xs">
                        <div class="text-center">
                            <div class="text-green-600 font-bold">{{ $stats['margin_breakdown']['high'] }}</div>
                            <div class="text-green-600">High</div>
                        </div>
                        <div class="text-center">
                            <div class="text-yellow-600 font-bold">{{ $stats['margin_breakdown']['medium'] }}</div>
                            <div class="text-yellow-600">Med</div>
                        </div>
                        <div class="text-center">
                            <div class="text-red-600 font-bold">{{ $stats['margin_breakdown']['low'] }}</div>
                            <div class="text-red-600">Low</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Potential -->
        @php $potential = $this->getTotalRevenuePotential(); @endphp
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Potential Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">@money($potential['total_revenue_potential'])</div>
                    <div class="text-sm text-blue-700">Potential Revenue</div>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">@money($potential['total_costs'])</div>
                    <div class="text-sm text-red-700">Total Costs</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">@money($potential['total_profit_potential'])</div>
                    <div class="text-sm text-green-700">Potential Profit</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ $potential['overall_margin'] }}%</div>
                    <div class="text-sm text-purple-700">Overall Margin</div>
                </div>
            </div>
        </div>

        <!-- Product Analysis Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Product Profit Analysis</h3>
                    <div>
                        <label for="sort_by" class="block text-xs font-medium text-gray-700 mb-1">Sort by</label>
                        <select wire:model.live="sortBy"
                                id="sort_by"
                                class="text-sm rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cost
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Margin %
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Margin $
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($this->getProductAnalysis() as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $product['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if ($product['price'])
                                        @money($product['price'])
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if ($product['cost'])
                                        @money($product['cost'])
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($product['margin_percentage'] !== null)
                                        <span class="font-medium text-{{ $product['color_class'] }}-600">
                                            {{ $product['margin_percentage'] }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($product['margin_amount'] !== null)
                                        <span class="font-medium text-{{ $product['color_class'] }}-600">
                                            @money($product['margin_amount'])
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($product['has_cost_data'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                               bg-{{ $product['color_class'] }}-100
                                               text-{{ $product['color_class'] }}-800">
                                            @if ($product['color_class'] === 'green')
                                                High Margin
                                            @elseif ($product['color_class'] === 'yellow')
                                                Medium Margin
                                            @else
                                                Low Margin
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Profitable Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Profitable Products</h3>

                @php $topProducts = $this->getTopProfitableProducts(); @endphp
                @if ($topProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($topProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $product['margin_percentage'] }}% margin</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">@money($product['margin_amount'])</p>
                                    <p class="text-xs text-gray-500">profit per unit</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-arrow-trending-up class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                        <p class="text-gray-500">No profitable products with cost data</p>
                    </div>
                @endif
            </div>

            <!-- Lowest Margin Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Products Needing Attention</h3>

                @php $lowProducts = $this->getLowestMarginProducts(); @endphp
                @if ($lowProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($lowProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-600">Needs price review</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-red-600">{{ $product['margin_percentage'] }}%</p>
                                    <p class="text-xs text-gray-500">margin</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-check-circle class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                        <p class="text-gray-500">No products with margin data</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Missing Cost Data -->
        @php $missingCost = $this->getMissingCostProducts(); @endphp
        @if ($missingCost->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Products Missing Cost Data</h3>
                <div class="bg-orange-50 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-orange-400" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-orange-800">Add cost data to improve profit analysis</h4>
                            <p class="text-sm text-orange-700 mt-1">
                                The following products don't have cost data. Add costs to their product record or create recipes with cost calculations.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($missingCost as $product)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                @if ($product['price'])
                                    <p class="text-xs text-gray-600">Price: @money($product['price'])</p>
                                @else
                                    <p class="text-xs text-red-600">No price set</p>
                                @endif
                            </div>
                            <div class="ml-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    No Cost
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Legend -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Margin Color Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center p-3 bg-green-100 rounded-lg">
                    <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-green-800">High Margin</p>
                        <p class="text-xs text-green-700">50% or higher</p>
                    </div>
                </div>
                <div class="flex items-center p-3 bg-yellow-100 rounded-lg">
                    <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Medium Margin</p>
                        <p class="text-xs text-yellow-700">30% - 49%</p>
                    </div>
                </div>
                <div class="flex items-center p-3 bg-red-100 rounded-lg">
                    <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-red-800">Low Margin</p>
                        <p class="text-xs text-red-700">Under 30%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>