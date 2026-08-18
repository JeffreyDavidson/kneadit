<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Week Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Weekly Prep Planner</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="week_start" class="block text-sm font-medium text-gray-700 mb-1">Week Starting</label>
                    <input type="date"
                           wire:model.live="selectedWeekStart"
                           id="week_start"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Week Range</label>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-md p-3">
                        {{ \Carbon\Carbon::parse($selectedWeekStart)->format('M j') }} -
                        {{ \Carbon\Carbon::parse($selectedWeekStart)->endOfWeek()->format('M j, Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Week Summary -->
        @if ($weeklyOrders->isNotEmpty())
            @php $summary = $this->getWeekSummary(); @endphp
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shopping-bag class="w-8 h-8 text-blue-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600">Total Orders</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $summary['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-tag class="w-8 h-8 text-green-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600">Total Items</p>
                            <p class="text-2xl font-bold text-green-900">{{ $summary['total_items'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="w-8 h-8 text-purple-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600">Prep Time</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $summary['total_prep_hours'] }}h</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-currency-dollar class="w-8 h-8 text-orange-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-600">Revenue</p>
                            <p class="text-2xl font-bold text-orange-900">@money($summary['total_revenue'])</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Product Summary -->
        @if ($weeklyOrders->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Summary for the Week</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Quantity
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Orders Count
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($this->getProductSummary() as $product)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $product['product_name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $product['total_quantity'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $product['orders_count'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Daily Prep Timeline -->
        @if ($weeklyOrders->isNotEmpty())
            <div class="space-y-6">
                @foreach ($weekDays as $day)
                    @php
                        $dayKey = $day->format('Y-m-d');
                        $dayOrders = $weeklyOrders->get($dayKey, collect());
                        $dayTimeline = $this->getTimelineView()->get($dayKey, collect());
                    @endphp

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 {{ $dayOrders->isNotEmpty() ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold {{ $dayOrders->isNotEmpty() ? 'text-blue-900' : 'text-gray-500' }}">
                                    {{ $day->format('l, F j') }}
                                    @if ($day->isToday())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                            Today
                                        </span>
                                    @endif
                                </h3>
                                @if ($dayOrders->isNotEmpty())
                                    <div class="text-sm text-blue-700">
                                        {{ $dayOrders->count() }} orders • {{ $dayOrders->sum(fn($order) => $order->orderItems->sum('quantity')) }} items
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($dayOrders->isNotEmpty())
                            <div class="p-6">
                                <!-- Prep Timeline -->
                                @if ($dayTimeline->isNotEmpty())
                                    <div class="mb-6">
                                        <h4 class="text-md font-semibold text-gray-900 mb-3">Prep Timeline</h4>
                                        <div class="space-y-3">
                                            @foreach ($dayTimeline as $task)
                                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <span class="text-yellow-800 font-bold">{{ $task['time'] }}</span> -
                                                            {{ $task['task'] }}
                                                        </p>
                                                        <p class="text-xs text-gray-600">
                                                            Order {{ $task['order'] }} • Duration: {{ $task['duration'] }} min •
                                                            Delivery: {{ $task['delivery_time'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Orders List -->
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 mb-3">Orders</h4>
                                    <div class="space-y-3">
                                        @foreach ($dayOrders as $order)
                                            <div class="border rounded-lg p-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center space-x-4">
                                                        <span class="text-sm font-bold text-gray-900">{{ $order->order_number }}</span>
                                                        <span class="text-sm text-gray-600">{{ $order->customer->name ?? 'Unknown Customer' }}</span>
                                                        <span class="text-sm text-gray-500">
                                                            {{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : 'Time not specified' }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">@money($order->total)</span>
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    @foreach ($order->orderItems as $item)
                                                        <span class="inline-block mr-4">{{ $item->quantity }}x {{ $item->product->name ?? 'Unknown Product' }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center text-gray-500">
                                <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2" stroke-width="2" />
                                <p>No orders scheduled</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <x-heroicon-o-arrow-up-tray class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No orders for this week</h3>
                    <p class="text-gray-500">No orders found for the week of {{ \Carbon\Carbon::parse($selectedWeekStart)->format('F j, Y') }}.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>