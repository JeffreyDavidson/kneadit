@use(App\Enums\Orders\OrderStatus)

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Calendar Header -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">{{ $this->getCurrentMonthName() }}</h2>
                <div class="flex space-x-2">
                    <button
                        wire:click="previousMonth"
                        class="rounded-md border border-gray-300 bg-white p-2 hover:bg-gray-50"
                    >
                        <x-heroicon-o-chevron-left class="h-5 w-5" stroke-width="2" />
                    </button>
                    <button
                        wire:click="nextMonth"
                        class="rounded-md border border-gray-300 bg-white p-2 hover:bg-gray-50"
                    >
                        <x-heroicon-o-chevron-right class="h-5 w-5" stroke-width="2" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Color Legend -->
        <div class="rounded-lg bg-white p-4 shadow">
            <h3 class="mb-2 text-sm font-medium text-gray-900">Order Volume Legend:</h3>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center space-x-2">
                    <div class="h-4 w-4 rounded bg-gray-100"></div>
                    <span>No orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="h-4 w-4 rounded bg-green-100"></div>
                    <span>1-5 orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="h-4 w-4 rounded bg-yellow-100"></div>
                    <span>6-10 orders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="h-4 w-4 rounded bg-red-100"></div>
                    <span>11+ orders</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="rounded-lg bg-white p-6 shadow">
            <!-- Day Headers -->
            <div class="mb-2 grid grid-cols-7 gap-1">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-2 text-center text-sm font-medium text-gray-700">{{ $day }}</div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-1">
                @foreach ($this->getCalendarDays() as $day)
                    <button
                        wire:click="selectDay('{{ $day['dateString'] }}')"
                        class="aspect-square p-2 text-sm rounded-md transition-colors cursor-pointer
                                   {{ $day['colorClass'] }}
                                   {{ !$day['isCurrentMonth'] ? 'opacity-50' : '' }}
                                   {{ $day['isToday'] ? 'ring-2 ring-primary-500' : '' }}
                                   {{ $selectedDate === $day['dateString'] ? 'ring-2 ring-blue-500' : '' }}"
                    >
                        <div class="flex h-full flex-col">
                            <span class="font-medium">{{ $day['date']->day }}</span>
                            @if ($day['orderCount'] > 0)
                                <span class="mt-auto text-xs">{{ $day['orderCount'] }}</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Selected Day Orders -->
        @if ($selectedDate && $selectedDayOrders->isNotEmpty())
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    Orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    <span class="ml-2 text-sm text-gray-500">({{ $selectedDayOrders->count() }} orders)</span>
                </h3>

                <div class="space-y-3">
                    @foreach ($selectedDayOrders as $order)
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $order->order_number }}</h4>
                                    <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                                    @if ($order->delivery_time)
                                        <p class="text-sm text-gray-500">
                                            Requested time: {{ \Carbon\Carbon::parse($order->delivery_time)->format('g:i A') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full {{ $order->status->badgeClasses() }}">
                                        {{ $order->status->getLabel() }}
                                    </span>
                                    <p class="mt-1 text-sm font-medium text-gray-900">@money($order->total)</p>
                                </div>
                            </div>

                            @if ($order->orderItems->isNotEmpty())
                                <div class="mt-3 border-t border-gray-100 pt-3">
                                    <p class="mb-1 text-sm text-gray-600">Items:</p>
                                    <div class="text-sm text-gray-800">
                                        @foreach ($order->orderItems as $item)
                                            <span class="mr-3 inline-block">
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
        @elseif ($selectedDate)
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="py-8 text-center text-gray-500">
                    <x-heroicon-o-clipboard class="mx-auto mb-4 h-12 w-12" stroke-width="2" />
                    <p class="text-lg">No orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
