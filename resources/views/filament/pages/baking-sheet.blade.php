<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->content }}
        
        @if(!empty($aggregatedItems))
            <div class="bg-white rounded-lg shadow print:shadow-none">
                {{-- Print Header --}}
                <div class="p-6 border-b print:border-black">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold">Baking Sheet</h1>
                        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 print:hidden">
                            Print Sheet
                        </button>
                    </div>
                    
                    <div class="text-lg font-semibold text-gray-700">
                        {{ $this->getFormattedDate() }}
                    </div>
                </div>
                
                {{-- Aggregated Summary --}}
                <div class="p-6 border-b print:border-black">
                    <h2 class="text-xl font-bold mb-4">Production Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($aggregatedItems as $item)
                            <div class="border rounded-lg p-4 print:border-black">
                                <div class="font-bold text-lg">{{ $item['product_name'] }}</div>
                                <div class="text-2xl font-bold text-blue-600">{{ $item['total_quantity'] }} units</div>
                                <div class="text-sm text-gray-600">
                                    {{ count($item['orders']) }} order{{ count($item['orders']) !== 1 ? 's' : '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Detailed Breakdown --}}
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Order Details</h2>
                    
                    @foreach($aggregatedItems as $item)
                        <div class="mb-6 border-b pb-4 last:border-b-0 print:border-black">
                            <h3 class="text-lg font-bold mb-2">{{ $item['product_name'] }}</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50 print:bg-gray-100">
                                            <th class="px-4 py-2 text-left font-semibold">Order #</th>
                                            <th class="px-4 py-2 text-left font-semibold">Customer</th>
                                            <th class="px-4 py-2 text-center font-semibold">Qty</th>
                                            <th class="px-4 py-2 text-left font-semibold">Notes</th>
                                            <th class="px-4 py-2 text-center font-semibold print:hidden">✓</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item['orders'] as $order)
                                            <tr class="border-t print:border-black">
                                                <td class="px-4 py-2 font-mono text-sm">{{ $order['order_number'] }}</td>
                                                <td class="px-4 py-2">{{ $order['customer_name'] }}</td>
                                                <td class="px-4 py-2 text-center font-bold">{{ $order['quantity'] }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $order['notes'] ?: '-' }}</td>
                                                <td class="px-4 py-2 text-center print:hidden">
                                                    <input type="checkbox" class="w-4 h-4">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Footer for print --}}
                <div class="p-6 border-t text-sm text-gray-600 print:border-black print:block hidden">
                    <div class="flex justify-between">
                        <span>Generated: {{ now()->format('M j, Y g:i A') }}</span>
                        <span>KneadIt Bakery Management</span>
                    </div>
                </div>
            </div>
        @else
            <x-filament::card>
                <div class="text-center py-8">
                    <div class="text-gray-500 text-lg">No orders found for {{ $this->getFormattedDate() }}</div>
                    <div class="text-sm text-gray-400 mt-2">
                        Select a different date or check if there are confirmed orders for this date.
                    </div>
                </div>
            </x-filament::card>
        @endif
    </div>
    
    <style>
        @media print {
            body { font-size: 12pt; }
            .print\\:hidden { display: none !important; }
            .print\\:block { display: block !important; }
            .print\\:border-black { border-color: black !important; }
            .print\\:bg-gray-100 { background-color: #f3f4f6 !important; }
            .print\\:shadow-none { box-shadow: none !important; }
            
            /* Ensure tables don't break across pages */
            table { page-break-inside: avoid; }
            tr { page-break-inside: avoid; }
            
            /* Add some margins for printing */
            @page { margin: 1in; }
        }
    </style>
</x-filament-panels::page>