<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Quantity
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Unit Price
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($getRecord()->orderItems as $item)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            @if ($item->product && $item->product->image)
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover"
                                         src="{{ Storage::url($item->product->image) }}"
                                         alt="{{ $item->product->name }}">
                                </div>
                            @else
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-photo class="w-5 h-5 text-gray-400" stroke-width="2" />
                                </div>
                            @endif
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->product ? $item->product->name : 'Product Not Found' }}
                                </div>
                                @if ($item->product && $item->product->description)
                                    <div class="text-sm text-gray-500">
                                        {{ Str::limit($item->product->description, 50) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->quantity }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        @money($item->price)
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        @money($item->price * $item->quantity)
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                    Subtotal:
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    @money($getRecord()->subtotal)
                </td>
            </tr>
            @if ($getRecord()->delivery_fee->isPositive())
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                        Delivery Fee:
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        @money($getRecord()->delivery_fee)
                    </td>
                </tr>
            @endif
            @if ($getRecord()->discount_amount->isPositive())
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-green-600">
                        Discount:
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-green-600">
                        -@money($getRecord()->discount_amount)
                    </td>
                </tr>
            @endif
            <tr class="border-t-2 border-gray-200">
                <td colspan="3" class="px-4 py-3 text-right text-lg font-bold text-gray-900">
                    Total:
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-lg font-bold text-gray-900">
                    @money($getRecord()->total)
                </td>
            </tr>
        </tfoot>
    </table>
</div>

@if ($getRecord()->orderItems->isEmpty())
    <div class="text-center py-8">
        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No items in this order</h3>
        <p class="text-gray-500">This order doesn't contain any items.</p>
    </div>
@endif