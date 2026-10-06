<x-filament-panels::page>
    <div class="space-y-6">
        <div class="mb-6">
            <label for="selectedYear" class="mb-2 block text-sm font-medium text-(--kn-ink-2)">Year</label>
            <select
                wire:model.live="selectedYear"
                class="focus:border-primary-500 focus:ring-primary-500 w-48 rounded-md border-(--kn-border) shadow-sm"
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
                <div class="rounded-lg bg-(--kn-success-tint) p-6">
                    <div class="text-sm font-medium text-(--kn-success)">Total Revenue</div>
                    <div class="text-3xl font-bold text-(--kn-success)">@money($totalRevenue)</div>
                </div>

                <div class="rounded-lg bg-(--kn-danger-tint) p-6">
                    <div class="text-sm font-medium text-(--kn-danger)">Total Expenses</div>
                    <div class="text-3xl font-bold text-(--kn-danger)">@money($totalExpenses)</div>
                </div>

                <div class="rounded-lg bg-(--kn-info-tint) p-6">
                    <div class="text-sm font-medium text-(--kn-info)">Net Profit</div>
                    <div @class(['text-3xl font-bold', 'text-(--kn-success)' => $netProfit >= 0, 'text-(--kn-danger)' => $netProfit < 0])>
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

                <div class="h-6 w-full rounded-full bg-(--kn-surface-hover)">
                    <div
                        class="flex h-6 items-center justify-center rounded-full bg-(--kn-honey) text-sm font-medium text-(--kn-on-honey) transition-all duration-300"
                        style="width: {{ min($revenueCapProgress, 100) }}%"
                    >
                        {{ number_format($revenueCapProgress, 1) }}%
                    </div>
                </div>

                @if ($revenueCapProgress > 80)
                    <div class="rounded border-l-4 border-yellow-400 bg-(--kn-warning-tint) p-4">
                        <div class="text-(--kn-warning)">
                            <strong>Warning:</strong> You're approaching the FL cottage food revenue cap! Remaining:
                            @money($revenueCap - $totalRevenue)
                        </div>
                    </div>
                @elseif ($revenueCapProgress >= 100)
                    <div class="rounded border-l-4 border-red-400 bg-(--kn-danger-tint) p-4">
                        <div class="text-(--kn-danger)">
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
                            <tr class="bg-(--kn-surface-sunken)">
                                <th class="px-4 py-2 text-left text-sm font-medium text-(--kn-ink-2)">Month</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-(--kn-ink-2)">Revenue</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-(--kn-ink-2)">Expenses</th>
                                <th class="px-4 py-2 text-right text-sm font-medium text-(--kn-ink-2)">Net Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyBreakdown as $month)
                                <tr class="border-t">
                                    <td class="px-4 py-3 font-medium">{{ $month['month_name'] }}</td>
                                    <td class="px-4 py-3 text-right text-(--kn-success)">@money($month['revenue'])</td>
                                    <td class="px-4 py-3 text-right text-(--kn-danger)">@money($month['expenses'])</td>
                                    <td @class(['px-4 py-3 text-right font-medium', 'text-(--kn-success)' => $month['net'] >= 0, 'text-(--kn-danger)' => $month['net'] < 0])>
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
                            <div class="flex items-center justify-between rounded-lg bg-(--kn-surface-sunken) p-3">
                                <span class="font-medium">{{ $expense['category'] }}</span>
                                <div class="text-right">
                                    <div class="font-bold">@money($expense['amount'])</div>
                                    <div class="text-sm text-(--kn-muted)">{{ $expense['percentage'] }}%</div>
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
                    <div class="rounded-lg bg-(--kn-warning-tint) p-6">
                        <div class="text-sm font-medium text-(--kn-warning)">COGS (Ingredients + Packaging)</div>
                        <div class="text-3xl font-bold text-(--kn-warning)">@money($cogsAmount)</div>
                        <div class="mt-2 text-sm text-(--kn-warning)">{{ $cogsPercentage }}% of total expenses</div>
                    </div>

                    @if ($totalRevenue > 0)
                        <div class="rounded-lg bg-(--kn-info-tint) p-4">
                            <div class="text-sm font-medium text-(--kn-info)">COGS Percentage of Revenue</div>
                            <div class="text-xl font-bold text-(--kn-info)">
                                {{ number_format(($cogsAmount / $totalRevenue) * 100, 1) }}%
                            </div>
                            @if (($cogsAmount / $totalRevenue) * 100 > 30)
                                <div class="mt-1 flex items-center gap-1 text-xs text-(--kn-info)">
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
