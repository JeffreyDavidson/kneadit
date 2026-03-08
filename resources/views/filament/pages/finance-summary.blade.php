<x-filament-panels::page>
    <div class="space-y-6">
        <div class="mb-6">
            <label for="selectedYear" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
            <select 
                wire:model.live="selectedYear"
                class="w-48 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
                @for($year = now()->year; $year >= (now()->year - 5); $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>

        {{-- Yearly P&L Overview --}}
        <x-filament::card>
            <x-slot name="heading">
                {{ $selectedYear }} Profit & Loss Overview
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 p-6 rounded-lg">
                    <div class="text-sm text-green-600 font-medium">Total Revenue</div>
                    <div class="text-3xl font-bold text-green-900">${{ number_format($totalRevenue, 2) }}</div>
                </div>
                
                <div class="bg-red-50 p-6 rounded-lg">
                    <div class="text-sm text-red-600 font-medium">Total Expenses</div>
                    <div class="text-3xl font-bold text-red-900">${{ number_format($totalExpenses, 2) }}</div>
                </div>
                
                <div class="bg-blue-50 p-6 rounded-lg">
                    <div class="text-sm text-blue-600 font-medium">Net Profit</div>
                    <div class="text-3xl font-bold {{ $netProfit >= 0 ? 'text-green-900' : 'text-red-900' }}">
                        ${{ number_format($netProfit, 2) }}
                    </div>
                </div>
            </div>
        </x-filament::card>

        {{-- Revenue Cap Tracker --}}
        <x-filament::card>
            <x-slot name="heading">
                FL Cottage Food Revenue Cap Tracker
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span>Current Revenue: <strong>${{ number_format($totalRevenue, 2) }}</strong></span>
                    <span>Cap: <strong>${{ number_format($revenueCap, 2) }}</strong></span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-6">
                    <div class="bg-gradient-to-r from-green-400 to-blue-500 h-6 rounded-full transition-all duration-300 flex items-center justify-center text-white text-sm font-medium"
                         style="width: {{ min($revenueCapProgress, 100) }}%">
                        {{ number_format($revenueCapProgress, 1) }}%
                    </div>
                </div>
                
                @if($revenueCapProgress > 80)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="text-yellow-800">
                            <strong>Warning:</strong> You're approaching the FL cottage food revenue cap! 
                            Remaining: ${{ number_format($revenueCap - $totalRevenue, 2) }}
                        </div>
                    </div>
                @elseif($revenueCapProgress >= 100)
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <div class="text-red-800">
                            <strong>Alert:</strong> You've exceeded the FL cottage food revenue cap by ${{ number_format($totalRevenue - $revenueCap, 2) }}!
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::card>

        {{-- Monthly Breakdown --}}
        @if($monthlyBreakdown->isNotEmpty())
        <x-filament::card>
            <x-slot name="heading">
                Monthly Breakdown - {{ $selectedYear }}
            </x-slot>
            
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
                        @foreach($monthlyBreakdown as $month)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-medium">{{ $month['month_name'] }}</td>
                            <td class="px-4 py-3 text-right text-green-600">${{ number_format($month['revenue'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600">${{ number_format($month['expenses'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $month['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($month['net'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Expense Breakdown --}}
            @if($expenseBreakdown->isNotEmpty())
            <x-filament::card>
                <x-slot name="heading">
                    Expense Breakdown by Category
                </x-slot>
                
                <div class="space-y-3">
                    @foreach($expenseBreakdown as $expense)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">{{ $expense['category'] }}</span>
                        <div class="text-right">
                            <div class="font-bold">${{ number_format($expense['amount'], 2) }}</div>
                            <div class="text-sm text-gray-600">{{ $expense['percentage'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-filament::card>
            @endif

            {{-- COGS Callout --}}
            <x-filament::card>
                <x-slot name="heading">
                    Cost of Goods Sold (COGS)
                </x-slot>
                
                <div class="space-y-4">
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="text-sm text-yellow-600 font-medium">COGS (Ingredients + Packaging)</div>
                        <div class="text-3xl font-bold text-yellow-900">${{ number_format($cogsAmount, 2) }}</div>
                        <div class="text-sm text-yellow-700 mt-2">{{ $cogsPercentage }}% of total expenses</div>
                    </div>
                    
                    @if($totalRevenue > 0)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm text-blue-600 font-medium">COGS Percentage of Revenue</div>
                        <div class="text-xl font-bold text-blue-900">
                            {{ number_format(($cogsAmount / $totalRevenue) * 100, 1) }}%
                        </div>
                        @if(($cogsAmount / $totalRevenue) * 100 > 30)
                            <div class="text-xs text-blue-700 mt-1">⚠️ Industry standard COGS is typically 25-30%</div>
                        @endif
                    </div>
                    @endif
                </div>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>