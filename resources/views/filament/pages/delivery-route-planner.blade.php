<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Delivery Route Planner</h2>
                <button wire:click="printRoute" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Route
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                    <input type="date" 
                           wire:model.live="selectedDate"
                           id="delivery_date"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Address</label>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-md p-3">{{ $storeAddress }}</p>
                </div>
            </div>
        </div>

        <!-- Route Statistics -->
        @if($deliveryOrders->isNotEmpty())
            @php $stats = $this->getRouteStats(); @endphp
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
                            <p class="text-2xl font-bold text-blue-900">{{ $stats['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-900">${{ number_format($stats['total_revenue'], 2) }}</p>
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
                            <p class="text-sm font-medium text-purple-600">Est. Total Time</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $stats['estimated_total_time'] }}m</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
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
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Route Map</h3>
            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                <p class="text-xl font-medium text-gray-600">Map integration coming soon</p>
                <p class="text-sm text-gray-500 mt-2">Interactive delivery route mapping will be added in a future update</p>
            </div>
        </div>

        <!-- Delivery Orders List -->
        @if($deliveryOrders->isNotEmpty())
            <div class="bg-white rounded-lg shadow overflow-hidden" id="printable-route">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Delivery Orders for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Delivery Address
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Distance
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($deliveryOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order['order_number'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $order['customer_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="max-w-xs">{{ $order['delivery_address'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $order['requested_time'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($order['total'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                               bg-{{ $order['distance_tier']['color'] }}-100 
                                               text-{{ $order['distance_tier']['color'] }}-800">
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
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No delivery orders found</h3>
                    <p class="text-gray-500">No orders with delivery addresses for {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('F j, Y') : 'the selected date' }}.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            @page {
                margin: 1in;
            }
            
            body * {
                visibility: hidden;
            }
            
            #printable-route, #printable-route * {
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

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('print-route', function () {
                window.print();
            });
        });
    </script>
</x-filament-panels::page>