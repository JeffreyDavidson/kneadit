<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->content }}
        
        <div class="relative">
            {{-- Customer List --}}
            <x-filament::card>
                <x-slot name="heading">
                    Customers ({{ count($customers) }})
                </x-slot>
                
                @if(count($customers) > 0)
                    <div class="divide-y">
                        @foreach($customers as $customer)
                            <div class="py-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                 wire:click="selectCustomer({{ $customer['id'] }})">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="font-semibold text-lg">{{ $customer['name'] }}</div>
                                        <div class="text-sm text-gray-600">{{ $customer['email'] }}</div>
                                        @if($customer['phone'])
                                            <div class="text-sm text-gray-600">{{ $customer['phone'] }}</div>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-sm font-medium">{{ $customer['orders_count'] }} orders</div>
                                        <div class="text-xs text-gray-500">Since {{ $customer['created_at'] }}</div>
                                    </div>
                                    
                                    <div class="ml-4">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-500">No customers found</div>
                        @if($search)
                            <div class="text-sm text-gray-400 mt-2">
                                Try adjusting your search terms
                            </div>
                        @endif
                    </div>
                @endif
            </x-filament::card>
        </div>
        
        {{-- Slide-over Customer Detail --}}
        @if($selectedCustomer)
            <div class="fixed inset-0 z-50 overflow-hidden" x-data="{ open: true }" x-show="open">
                <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="closeCustomerDetail()"></div>
                
                <div class="fixed inset-y-0 right-0 max-w-2xl w-full bg-white shadow-xl overflow-y-auto">
                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">{{ $selectedCustomer['name'] }}</h2>
                            <button wire:click="closeCustomerDetail()" 
                                    class="p-2 rounded-md hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Customer Info --}}
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Email</div>
                                    <div>{{ $selectedCustomer['email'] }}</div>
                                </div>
                                
                                @if($selectedCustomer['phone'])
                                    <div>
                                        <div class="text-sm font-medium text-gray-600">Phone</div>
                                        <div>{{ $selectedCustomer['phone'] }}</div>
                                    </div>
                                @endif
                                
                                @if($selectedCustomer['address'])
                                    <div class="md:col-span-2">
                                        <div class="text-sm font-medium text-gray-600">Address</div>
                                        <div>{{ $selectedCustomer['address'] }}</div>
                                    </div>
                                @endif
                                
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Customer Since</div>
                                    <div>{{ $selectedCustomer['created_at']->format('F j, Y') }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Total Orders</div>
                                    <div>{{ $selectedCustomer['total_orders'] }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm font-medium text-gray-600">Total Spent</div>
                                    <div>${{ number_format($selectedCustomer['total_spent'], 2) }}</div>
                                </div>
                                
                                @if($selectedCustomer['last_order'])
                                    <div>
                                        <div class="text-sm font-medium text-gray-600">Last Order</div>
                                        <div>{{ $selectedCustomer['last_order']->format('M j, Y') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Recent Orders --}}
                        @if(count($customerOrders) > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-3">Recent Orders</h3>
                                <div class="space-y-3">
                                    @foreach($customerOrders as $order)
                                        <div class="border rounded-lg p-3">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium">{{ $order['order_number'] }}</div>
                                                    <div class="text-sm text-gray-600">
                                                        {{ $order['requested_date'] ? 
                                                           Carbon\Carbon::parse($order['requested_date'])->format('M j, Y') : 
                                                           $order['created_at']->format('M j, Y') }}
                                                    </div>
                                                </div>
                                                
                                                <div class="text-right">
                                                    <div class="font-medium">${{ number_format($order['total'], 2) }}</div>
                                                    <div class="text-xs">
                                                        <span class="px-2 py-1 rounded-full text-xs {{ 
                                                            $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : 
                                                            ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                                        }}">
                                                            {{ ucfirst($order['status']) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        {{-- Customer Notes --}}
                        <div>
                            <h3 class="text-lg font-semibold mb-3">Notes</h3>
                            
                            @if(count($customerNotes) > 0)
                                <div class="space-y-3">
                                    @foreach($customerNotes as $note)
                                        <div class="border rounded-lg p-3">
                                            <div class="text-sm">{{ $note['note'] }}</div>
                                            <div class="text-xs text-gray-500 mt-2">
                                                By {{ $note['created_by'] }} on {{ $note['created_at']->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-gray-500 text-sm">No notes recorded for this customer.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <script>
        // Alpine.js for slide-over animation
        document.addEventListener('alpine:init', () => {
            Alpine.data('slideOver', () => ({
                open: true,
                init() {
                    this.$nextTick(() => {
                        this.open = true;
                    });
                }
            }));
        });
    </script>
</x-filament-panels::page>