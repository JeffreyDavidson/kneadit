<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Calendar Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">{{ $this->getCurrentMonthName() }}</h2>
                <div class="flex space-x-2">
                    <button wire:click="previousMonth" 
                            class="p-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button wire:click="nextMonth" 
                            class="p-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Color Legend -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Order Volume Legend:</h3>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-gray-100 rounded"></div>
                    <span>No orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-100 rounded"></div>
                    <span>1-5 orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-100 rounded"></div>
                    <span>6-10 orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-100 rounded"></div>
                    <span>11+ orders</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-2 text-center text-sm font-medium text-gray-700">{{ $day }}</div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-1">
                @foreach($this->getCalendarDays() as $day)
                    <button wire:click="selectDay('{{ $day['dateString'] }}')" 
                            class="aspect-square p-2 text-sm rounded-md transition-colors cursor-pointer
                                   {{ $day['colorClass'] }}
                                   {{ !$day['isCurrentMonth'] ? 'opacity-50' : '' }}
                                   {{ $day['isToday'] ? 'ring-2 ring-primary-500' : '' }}
                                   {{ $selectedDate === $day['dateString'] ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="flex flex-col h-full">
                            <span class="font-medium">{{ $day['date']->day }}</span>
                            @if($day['orderCount'] > 0)
                                <span class="text-xs mt-auto">{{ $day['orderCount'] }}</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Selected Day Orders -->
        @if($selectedDate && $selectedDayOrders->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    <span class="text-sm text-gray-500 ml-2">({{ $selectedDayOrders->count() }} orders)</span>
                </h3>

                <div class="space-y-3">
                    @foreach($selectedDayOrders as $order)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $order->order_number }}</h4>
                                    <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                                    @if($order->requested_time)
                                        <p class="text-sm text-gray-500">
                                            Requested time: {{ \Carbon\Carbon::parse($order->requested_time)->format('g:i A') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full
                                        {{ $order->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    <p class="text-sm font-medium text-gray-900 mt-1">${{ number_format($order->total, 2) }}</p>
                                </div>
                            </div>
                            
                            @if($order->orderItems->isNotEmpty())
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-sm text-gray-600 mb-1">Items:</p>
                                    <div class="text-sm text-gray-800">
                                        @foreach($order->orderItems as $item)
                                            <span class="inline-block mr-3">
                                                {{ $item->quantity }}× {{ $item->product->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($selectedDate)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-lg">No orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>