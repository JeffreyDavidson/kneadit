<x-filament-panels::page>
    <div class="space-y-6">
        <div class="mb-6">
            <label for="selectedYear" class="mb-2 block text-sm font-medium text-gray-700">Year</label>
            <select
                wire:model.live="selectedYear"
                class="focus:border-primary-500 focus:ring-primary-500 w-48 rounded-md border-gray-300 shadow-sm"
            >
                @for ($year = now()->year; $year >= (now()->year - 5); $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>

        {{-- Yearly P&L Overview --}}
        <x-filament::card>
            <x-slot name="heading">{{ $selectedYear }} Profit & Loss Overview</x-slot>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-lg bg-green-50 p-6">
                    <div class="text-sm font-medium text-green-600">Total Revenue</div>
                    <div class="text-3xl font-bold text-green-900">@money($totalRevenue)</div>
                </div>

                <div class="rounded-lg bg-red-50 p-6">
                    <div class="text-sm font-medium text-red-600">Total Expenses</div>
                    <div class="text-3xl font-bold text-red-900">@money($totalExpenses)</div>
                </div>

                <div class="rounded-lg bg-blue-50 p-6">
                    <div class="text-sm font-medium text-blue-600">Net Profit</div>
                    <div class="text-3xl font-bold {{ $netProfit >= 0 ? 'text-green-900' : 'text-red-900' }}">
                        @money($netProfit)
                    </div>
                </div>
            </div>
        </x-filament::card>

        {{-- Revenue Cap Tracker --}}
        <x-filament::card>
            <x-slot name="heading">FL Cottage Food Revenue Cap Tracker</x-slot>

            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span
                        >Current Revenue: <strong>@money($totalRevenue)</strong
                    ></span>
                    <span
                        >Cap: <strong>@money($revenueCap)</strong
                    ></span>
                </div>

                <div class="h-6 w-full rounded-full bg-gray-200">
                    <div
                        class="flex h-6 items-center justify-center rounded-full bg-gradient-to-r from-green-400 to-blue-500 text-sm font-medium text-white transition-all duration-300"
                        style="width: {{ min($revenueCapProgress, 100) }}%"
                    >
                        {{ number_format($revenueCapProgress, 1) }}%
                    </div>
                </div>

                @if ($revenueCapProgress > 80)
                    <div class="rounded border-l-4 border-yellow-400 bg-yellow-50 p-4">
                        <div class="text-yellow-800">
                            <strong>Warning:</strong> You're approaching the FL cottage food revenue cap! Remaining:
                            @money($revenueCap - $totalRevenue)
                        </div>
                    </div>
                @elseif ($revenueCapProgress >= 100)
                    <div class="rounded border-l-4 border-red-400 bg-red-50 p-4">
                        <div class="text-red-800">
                            <strong>Alert:</strong> You've exceeded the FL cottage food revenue cap by
                            @money($totalRevenue - $revenueCap)
                            !
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::card>

        {{-- Monthly Breakdown --}}
        @if ($monthlyBreakdown->isNotEmpty())
            <x-filament::card>
                <x-slot name="heading">Monthly Breakdown - {{ $selectedYear }}</x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Month</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Revenue</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Expenses</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Net Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyBreakdown as $month)
                                <tr class="border-t">
                                    <td class="px-4 py-3 font-medium">{{ $month['month_name'] }}</td>
                                    <td class="px-4 py-3 text-right text-green-600">@money($month['revenue'])</td>
                                    <td class="px-4 py-3 text-right text-red-600">@money($month['expenses'])</td>
                                    <td class="px-4 py-3 text-right font-medium {{ $month['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @money($month['net'])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Expense Breakdown --}}
            @if ($expenseBreakdown->isNotEmpty())
                <x-filament::card>
                    <x-slot name="heading">Expense Breakdown by Category</x-slot>

                    <div class="space-y-3">
                        @foreach ($expenseBreakdown as $expense)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                <span class="font-medium">{{ $expense['category'] }}</span>
                                <div class="text-right">
                                    <div class="font-bold">@money($expense['amount'])</div>
                                    <div class="text-sm text-gray-600">{{ $expense['percentage'] }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::card>
            @endif

            {{-- COGS Callout --}}
            <x-filament::card>
                <x-slot name="heading">Cost of Goods Sold (COGS)</x-slot>

                <div class="space-y-4">
                    <div class="rounded-lg bg-yellow-50 p-6">
                        <div class="text-sm font-medium text-yellow-600">COGS (Ingredients + Packaging)</div>
                        <div class="text-3xl font-bold text-yellow-900">@money($cogsAmount)</div>
                        <div class="mt-2 text-sm text-yellow-700">{{ $cogsPercentage }}% of total expenses</div>
                    </div>

                    @if ($totalRevenue > 0)
                        <div class="rounded-lg bg-blue-50 p-4">
                            <div class="text-sm font-medium text-blue-600">COGS Percentage of Revenue</div>
                            <div class="text-xl font-bold text-blue-900">
                                {{ number_format(($cogsAmount / $totalRevenue) * 100, 1) }}%
                            </div>
                            @if (($cogsAmount / $totalRevenue) * 100 > 30)
                                <div class="mt-1 flex items-center gap-1 text-xs text-blue-700">
                                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                                    Industry standard COGS is typically 25-30%
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>
