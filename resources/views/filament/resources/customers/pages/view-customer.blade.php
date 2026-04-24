<x-filament-panels::page>
    @php
        /** @var array<string, mixed> $detail */
        $stats = $detail['stats'];
        /** @var array<int, array<string, mixed>> $orders */
        $orders = $detail['orders'];
        /** @var array<int, array<string, mixed>> $notes */
        $notes = $detail['notes'];
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ $detail['name'] }}</x-slot>
            <x-slot name="description">{{ $detail['email'] ?? '—' }} · {{ $detail['phone'] ?? '—' }}</x-slot>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total orders</div>
                    <div class="text-2xl font-semibold">{{ $stats['total_orders'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Lifetime value</div>
                    <div class="text-2xl font-semibold">${{ number_format((float) $stats['total_spent'], 2) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Avg order value</div>
                    <div class="text-2xl font-semibold">${{ number_format((float) $stats['avg_order_value'], 2) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Last order</div>
                    <div class="text-2xl font-semibold">{{ $stats['last_order'] ?? '—' }}</div>
                </div>
            </div>

            @if (! empty($detail['address']))
                <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Address</div>
                    <div class="text-sm">{{ $detail['address'] }}</div>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Recent orders</x-slot>
            <x-slot name="description">{{ count($orders) }} total</x-slot>

            @if ($orders === [])
                <div class="text-sm text-gray-500 dark:text-gray-400">No orders yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pr-4">Order</th>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Payment</th>
                                <th class="py-2 pr-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="py-2 pr-4 font-mono">{{ $order['order_number'] }}</td>
                                    <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">{{ $order['date'] ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $order['status'] }}</td>
                                    <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">{{ $order['payment_status'] }}</td>
                                    <td class="py-2 pr-4 text-right">{{ $order['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Notes</x-slot>
            <x-slot name="description">{{ count($notes) }} total</x-slot>

            @if ($notes === [])
                <div class="text-sm text-gray-500 dark:text-gray-400">No notes yet.</div>
            @else
                <ul class="space-y-3">
                    @foreach ($notes as $note)
                        <li class="rounded border border-gray-200 p-3 dark:border-gray-700">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $note['created_by'] }} · {{ $note['created_at'] ?? '—' }}
                            </div>
                            <div class="mt-1 text-sm whitespace-pre-wrap">{{ $note['note'] }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
