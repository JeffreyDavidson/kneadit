<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->content }}
        
        {{-- Yearly Summary Card --}}
        <x-filament::card>
            <x-slot name="heading">
                {{ $selectedYear }} Financial Summary
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-600 font-medium">Total Income</div>
                    <div class="text-2xl font-bold text-green-900">${{ number_format($yearlyData['total_income'] ?? 0, 2) }}</div>
                </div>
                
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm text-red-600 font-medium">Total Expenses</div>
                    <div class="text-2xl font-bold text-red-900">${{ number_format($yearlyData['total_expenses'] ?? 0, 2) }}</div>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-blue-600 font-medium">Net Profit</div>
                    <div class="text-2xl font-bold {{ ($yearlyData['net_profit'] ?? 0) >= 0 ? 'text-green-900' : 'text-red-900' }}">
                        ${{ number_format($yearlyData['net_profit'] ?? 0, 2) }}
                    </div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-sm text-purple-600 font-medium">Profit Margin</div>
                    <div class="text-2xl font-bold text-purple-900">
                        {{ number_format($yearlyData['profit_margin'] ?? 0, 1) }}%
                    </div>
                </div>
            </div>
        </x-filament::card>
        
        {{-- Revenue Cap Tracker --}}
        <x-filament::card>
            <x-slot name="heading">
                FL Cottage Food Revenue Cap (${{ number_format($revenueCap) }})
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between text-sm">
                    <span>Current Revenue: ${{ number_format($totalRevenue, 2) }}</span>
                    <span>{{ number_format($this->getRevenueCapPercentage(), 1) }}% of cap</span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-green-400 to-blue-500 h-4 rounded-full transition-all duration-300"
                         style="width: {{ min($this->getRevenueCapPercentage(), 100) }}%">
                    </div>
                </div>
                
                @if($this->getRevenueCapPercentage() > 80)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="text-yellow-800">
                            <strong>Warning:</strong> You're approaching the FL cottage food revenue cap!
                        </div>
                    </div>
                @elseif($this->getRevenueCapPercentage() >= 100)
                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="text-red-800">
                            <strong>Alert:</strong> You've exceeded the FL cottage food revenue cap!
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::card>
        
        {{-- Monthly Breakdown --}}
        @if(!empty($monthlyData))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::card>
                <x-slot name="heading">
                    {{ $monthlyData['month_name'] }} - Income by Source
                </x-slot>
                
                @if(!empty($monthlyData['income_by_source']))
                    <div class="space-y-3">
                        @foreach($monthlyData['income_by_source'] as $source => $amount)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">{{ App\Models\Income::SOURCES[$source] ?? $source }}</span>
                                <span class="font-bold">${{ number_format($amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No income recorded for this month.</p>
                @endif
            </x-filament::card>
            
            <x-filament::card>
                <x-slot name="heading">
                    {{ $monthlyData['month_name'] }} - Expenses by Category
                </x-slot>
                
                @if(!empty($monthlyData['expenses_by_category']))
                    <div class="space-y-3">
                        @foreach($monthlyData['expenses_by_category'] as $category => $amount)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">{{ App\Models\Expense::CATEGORIES[$category] ?? $category }}</span>
                                <span class="font-bold">${{ number_format($amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No expenses recorded for this month.</p>
                @endif
            </x-filament::card>
        </div>
        @endif
    </div>
</x-filament-panels::page>