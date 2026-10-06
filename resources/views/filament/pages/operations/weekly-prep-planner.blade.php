<x-filament-panels::page>
    @php
        $weeklyOrders = $this->weekData->weeklyOrders;
        $weekDays = $this->weekData->weekDays;
    @endphp

    <div class="space-y-6">
        <!-- Week Selection -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-(--kn-ink)">Weekly Prep Planner</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="week_start" class="mb-1 block text-sm font-medium text-(--kn-ink-2)"
                        >Week Starting</label>
                    <input
                        type="date"
                        wire:model.live="selectedWeekStart"
                        id="week_start"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-(--kn-border) shadow-sm"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-(--kn-ink-2)">Week Range</label>
                    <p class="rounded-md bg-(--kn-surface-sunken) p-3 text-sm text-(--kn-muted)">
                        {{ \Carbon\Carbon::parse($selectedWeekStart)->format('M j') }} - {{ \Carbon\Carbon::parse($selectedWeekStart)->endOfWeek()->format('M j, Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Week Summary -->
        @if ($weeklyOrders->isNotEmpty())
            @php $summary = $this->getWeekSummary(); @endphp
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-(--kn-info-tint) p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shopping-bag class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-(--kn-info)">Total Orders</p>
                            <p class="text-2xl font-bold text-(--kn-info)">{{ $summary['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-(--kn-success-tint) p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-tag class="h-8 w-8 text-(--kn-success)" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-(--kn-success)">Total Items</p>
                            <p class="text-2xl font-bold text-(--kn-success)">{{ $summary['total_items'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-purple-50 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-8 w-8 text-purple-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600">Prep Time</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $summary['total_prep_hours'] }}h</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-(--kn-warning-tint) p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-currency-dollar class="h-8 w-8 text-(--kn-warning)" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-(--kn-warning)">Revenue</p>
                            <p class="text-2xl font-bold text-(--kn-warning)">@money($summary['total_revenue'])</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Product Summary -->
        @if ($weeklyOrders->isNotEmpty())
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Product Summary for the Week</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-(--kn-border)">
                        <thead class="bg-(--kn-surface-sunken)">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                    Product
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                    Total Quantity
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                                    Orders Count
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-(--kn-border) bg-(--kn-surface)">
                            @foreach ($this->getProductSummary() as $product)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-(--kn-ink)">
                                        {{ $product['product_name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-(--kn-muted)">
                                        {{ $product['total_quantity'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-(--kn-muted)">{{ $product['orders_count'] }}</td>
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

                    <div class="overflow-hidden rounded-lg bg-(--kn-surface) shadow">
                        <div class="px-6 py-4 border-b border-(--kn-border) {{ $dayOrders->isNotEmpty() ? 'bg-(--kn-info-tint)' : 'bg-(--kn-surface-sunken)' }}">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold {{ $dayOrders->isNotEmpty() ? 'text-(--kn-info)' : 'text-(--kn-muted)' }}">
                                    {{ $day->format('l, F j') }}
                                    @if ($day->isSameDay(resolve(\App\Services\Scheduling\BakeryClock::class)->today()))
                                        <span class="ml-2 inline-flex items-center rounded-full bg-(--kn-success-tint) px-2.5 py-0.5 text-xs font-medium text-(--kn-success)">
                                            Today
                                        </span>
                                    @endif
                                </h3>
                                @if ($dayOrders->isNotEmpty())
                                    <div class="text-sm text-(--kn-info)">
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
                                        <h4 class="text-md mb-3 font-semibold text-(--kn-ink)">Prep Timeline</h4>
                                        <div class="space-y-3">
                                            @foreach ($dayTimeline as $task)
                                                <div class="flex items-center justify-between rounded-lg border-l-4 border-yellow-400 bg-(--kn-warning-tint) p-3">
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-(--kn-ink)">
                                                            <span class="font-bold text-(--kn-warning)">{{ $task['time'] }}</span>
                                                            - {{ $task['task'] }}
                                                        </p>
                                                        <p class="text-xs text-(--kn-muted)">
                                                            Order {{ $task['order'] }} • Duration: {{ $task['duration'] }} min
                                                            • Delivery: {{ $task['delivery_time'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Orders List -->
                                <div>
                                    <h4 class="text-md mb-3 font-semibold text-(--kn-ink)">Orders</h4>
                                    <div class="space-y-3">
                                        @foreach ($dayOrders as $order)
                                            <div class="rounded-lg border p-4">
                                                <div class="mb-2 flex items-center justify-between">
                                                    <div class="flex items-center space-x-4">
                                                        <span class="text-sm font-bold text-(--kn-ink)">{{ $order->order_number }}</span>
                                                        <span class="text-sm text-(--kn-muted)">{{ $order->customer->name ?? 'Unknown Customer' }}</span>
                                                        <span class="text-sm text-(--kn-muted)">
                                                            {{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : 'Time not specified' }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-medium text-(--kn-ink)">
                                                        @money($order->total)
                                                    </span>
                                                </div>
                                                <div class="text-sm text-(--kn-muted)">
                                                    @foreach ($order->orderItems as $item)
                                                        <span class="mr-4 inline-block">{{ $item->quantity }}x {{ $item->product->name ?? 'Unknown Product' }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center text-(--kn-muted)">
                                <x-heroicon-o-inbox class="mx-auto mb-2 h-8 w-8" stroke-width="2" />
                                <p>No orders scheduled</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <div class="text-center">
                    <x-heroicon-o-arrow-up-tray class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                    <h3 class="mb-2 text-lg font-medium text-(--kn-ink)">No orders for this week</h3>
                    <p class="text-(--kn-muted)">
                        No orders found for the week of {{ \Carbon\Carbon::parse($selectedWeekStart)->format('F j, Y') }}.
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
