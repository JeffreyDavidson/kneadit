@php
/** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
/** @var \App\Models\Customers\Customer $customer */
/** @var \App\Services\Settings\TenantSettings $settings */
@endphp
<x-layouts.storefront>
    <section class="py-16 px-4 bg-warm-50 min-h-[60vh]">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="font-display text-3xl text-warm-900">Your orders</h1>
                <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-warm-700 hover:underline">
                    &larr; Back to dashboard
                </a>
            </div>

            @if ($orders->isEmpty())
                <div class="card p-8 text-center">
                    <p class="text-warm-600 mb-4">You haven't placed any orders yet.</p>
                    <a href="{{ route('order.create') }}" class="inline-block text-sm font-semibold text-warm-800 hover:underline">
                        Start an order &rarr;
                    </a>
                </div>
            @else
                <div class="card overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-warm-100 text-xs uppercase tracking-wider text-warm-700">
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-warm-200">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-warm-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('order.confirmation', $order) }}" class="font-semibold text-warm-900 hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        <p class="text-xs text-warm-600">{{ $order->orderItems->count() }} item{{ $order->orderItems->count() === 1 ? '' : 's' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-warm-700">
                                        {{ $order->created_at?->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $order->status->getLabel() }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-warm-900">
                                        @money($order->total)
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex gap-3 justify-end">
                                            <a href="{{ route('order.create') }}?reorder={{ $order->order_number }}"
                                               class="text-xs font-semibold text-warm-700 hover:underline">
                                                Reorder
                                            </a>
                                            <a href="{{ route('order.track') }}?number={{ $order->order_number }}"
                                               class="text-xs font-semibold text-warm-700 hover:underline">
                                                Track
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
