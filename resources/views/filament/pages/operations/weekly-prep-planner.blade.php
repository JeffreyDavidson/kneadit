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
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
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
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
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
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
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
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
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
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-1.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H8m12 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4h.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h1.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H16"></path>
                                </svg>
                                <p>No orders scheduled</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6l4-4m0 0l4 4m-4-4v11"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No orders for this week</h3>
                    <p class="text-gray-500">No orders found for the week of {{ \Carbon\Carbon::parse($selectedWeekStart)->format('F j, Y') }}.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>