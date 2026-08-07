<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date Selection -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Delivery Route Planner</h2>
                <button
                    wire:click="printRoute"
                    class="focus:ring-primary-500 inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                >
                    <x-heroicon-o-printer class="mr-2 h-4 w-4" stroke-width="2" />
                    Print Route
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="delivery_date" class="mb-1 block text-sm font-medium text-gray-700"
                        >Delivery Date</label>
                    <input
                        type="date"
                        wire:model.live="selectedDate"
                        id="delivery_date"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store Address</label>
                    <p class="rounded-md bg-gray-50 p-3 text-sm text-gray-600">{{ $storeAddress }}</p>
                </div>
            </div>
        </div>

        <!-- Route Statistics -->
        @if ($deliveryOrders->isNotEmpty())
            @php $stats = $this->getRouteStats(); @endphp
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-blue-50 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shopping-bag class="h-8 w-8 text-blue-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600">Total Orders</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $stats['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-green-50 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-currency-dollar class="h-8 w-8 text-green-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-900">@money($stats['total_revenue'])</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-purple-50 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-8 w-8 text-purple-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600">Est. Total Time</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $stats['estimated_total_time'] }}m</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-orange-50 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-map class="h-8 w-8 text-orange-600" stroke-width="2" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-600">Avg Distance</p>
                            <p class="text-2xl font-bold text-orange-900">{{ $stats['average_distance_time'] }}m</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Map Placeholder -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Route Map</h3>
            <div class="rounded-lg bg-gray-100 p-8 text-center">
                <x-heroicon-o-map class="mx-auto mb-4 h-16 w-16 text-gray-400" stroke-width="2" />
                <p class="text-xl font-medium text-gray-600">Map integration coming soon</p>
                <p class="mt-2 text-sm text-gray-500">
                    Interactive delivery route mapping will be added in a future update
                </p>
            </div>
        </div>

        <!-- Delivery Orders List -->
        @if ($deliveryOrders->isNotEmpty())
            <div class="overflow-hidden rounded-lg bg-white shadow" id="printable-route">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Delivery Orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Order
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Delivery Address
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                                    Distance
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($deliveryOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                                        {{ $order['order_number'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600">
                                        {{ $order['customer_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="max-w-xs">{{ $order['delivery_address'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600">
                                        {{ $order['delivery_time'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                                        @money($order['total'])
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                               bg-{{ $order['distance_tier']['color'] }}-100
                                               text-{{ $order['distance_tier']['color'] }}-800"
                                        >
                                            {{ $order['distance_tier']['tier'] }} (~{{ $order['distance_tier']['estimated_minutes'] }}m)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="text-center">
                    <x-heroicon-o-shopping-bag class="mx-auto mb-4 h-12 w-12 text-gray-400" stroke-width="2" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">No delivery orders found</h3>
                    <p class="text-gray-500">
                        No orders with delivery addresses for {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('F j, Y') : 'the selected date' }}.
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Print Styles -->
    <style @cspnonce>
        @media print {
            @page {
                margin: 1in;
            }

            body * {
                visibility: hidden;
            }

            #printable-route,
            #printable-route * {
                visibility: visible;
            }

            #printable-route {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
            }

            .bg-gray-50 {
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact;
            }

            .border-gray-200 {
                border-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <script @cspnonce>
        document.addEventListener('livewire:load', function () {
            Livewire.on('print-route', function () {
                window.print();
            });
        });
    </script>
</x-filament-panels::page>
