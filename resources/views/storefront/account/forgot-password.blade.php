@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
    /** @var \App\Models\Customers\Customer $customer */
    /** @var \App\Services\Settings\TenantSettings $settings */
@endphp
<x-layouts.storefront>
    <section class="bg-warm-50 min-h-[60vh] px-4 py-16">
        <div class="mx-auto max-w-4xl">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="font-display text-warm-900 text-3xl">Your orders</h1>
                <a href="{{ route('account.dashboard') }}" class="text-warm-700 text-sm font-semibold hover:underline">
                    &larr; Back to dashboard
                </a>
            </div>

            @if ($orders->isEmpty())
                <div class="card p-8 text-center">
                    <p class="text-warm-600 mb-4">You haven't placed any orders yet.</p>
                    <a
                        href="{{ route('order.create') }}"
                        class="text-warm-800 inline-block text-sm font-semibold hover:underline"
                    >
                        Start an order &rarr;
                    </a>
                </div>
            @else
                {{-- Mobile: stacked cards. Desktop: table. --}}
                <div class="space-y-3 md:hidden">
                    @foreach ($orders as $order)
                        <div class="card p-4">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a
                                        href="{{ route('order.confirmation', $order) }}"
                                        class="text-warm-900 font-semibold hover:underline"
                                    >
                                        #{{ $order->order_number }}
                                    </a>
                                    <p class="text-warm-600 mt-0.5 text-xs">
                                        {{ $order->created_at?->format('M j, Y') }} &middot; {{ $order->orderItems->count() }} item{{ $order->orderItems->count() === 1 ? '' : 's' }}
                                    </p>
                                </div>
                                <p class="text-warm-900 font-semibold whitespace-nowrap">@money($order->total)</p>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-warm-700">{{ $order->status->getLabel() }}</span>
                                <div class="flex gap-3">
                                    <a
                                        href="{{ route('order.create') }}?reorder={{ $order->order_number }}"
                                        class="text-warm-700 font-semibold hover:underline"
                                    >
                                        Reorder
                                    </a>
                                    <a
                                        href="{{ route('order.track') }}?number={{ $order->order_number }}"
                                        class="text-warm-700 font-semibold hover:underline"
                                    >
                                        Track
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card hidden overflow-hidden md:block">
                    <table class="w-full text-left">
                        <thead class="bg-warm-100 text-warm-700 text-xs tracking-wider uppercase">
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-warm-200 divide-y">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-warm-50">
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route('order.confirmation', $order) }}"
                                            class="text-warm-900 font-semibold hover:underline"
                                        >
                                            #{{ $order->order_number }}
                                        </a>
                                        <p class="text-warm-600 text-xs">
                                            {{ $order->orderItems->count() }} item{{ $order->orderItems->count() === 1 ? '' : 's' }}
                                        </p>
                                    </td>
                                    <td class="text-warm-700 px-4 py-3 text-sm">
                                        {{ $order->created_at?->format('M j, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $order->status->getLabel() }}</td>
                                    <td class="text-warm-900 px-4 py-3 text-right font-semibold">
                                        @money($order->total)
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a
                                                href="{{ route('order.create') }}?reorder={{ $order->order_number }}"
                                                class="text-warm-700 text-xs font-semibold hover:underline"
                                            >
                                                Reorder
                                            </a>
                                            <a
                                                href="{{ route('order.track') }}?number={{ $order->order_number }}"
                                                class="text-warm-700 text-xs font-semibold hover:underline"
                                            >
                                                Track
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
