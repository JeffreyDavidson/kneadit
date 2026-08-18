<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Controls --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                    <input type="date" wire:model.live="startDate" wire:change="generateList"
                        class="mt-1 block rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                    <input type="date" wire:model.live="endDate" wire:change="generateList"
                        class="mt-1 block rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm">
                </div>
                <button wire:click="toggleUpcoming"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium shadow-sm
                        {{ $includeUpcoming
                            ? 'bg-primary-600 text-white hover:bg-primary-500'
                            : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700' }}">
                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                    {{ $includeUpcoming ? 'Upcoming Orders Included' : 'Include Upcoming Orders' }}
                </button>
            </div>
        </div>

        @if ($supplierGroups->isEmpty())
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12 text-center">
                <x-heroicon-o-check-circle class="mx-auto text-success-500 w-12 h-12" />
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">All stocked up!</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No ingredients are currently below their low stock threshold.</p>
            </div>
        @endif

        {{-- Supplier Groups --}}
        @foreach ($supplierGroups as $key => $group)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                {{-- Supplier Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-truck class="text-gray-400 w-5 h-5" />
                            {{ $group['supplier']['name'] }}
                        </h3>
                        @if ($group['supplier']['email'] || $group['supplier']['phone'])
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if ($group['supplier']['email'])
                                    {{ $group['supplier']['email'] }}
                                @endif
                                @if ($group['supplier']['email'] && $group['supplier']['phone']) &bull; @endif
                                @if ($group['supplier']['phone'])
                                    {{ $group['supplier']['phone'] }}
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            @money($group['total'])
                        </span>
                        @if ($group['supplier']['id'] && $group['supplier']['email'])
                            <button wire:click="sendPurchaseOrder({{ $group['supplier']['id'] }})"
                                wire:confirm="Send purchase order to {{ $group['supplier']['email'] }}?"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500">
                                <x-heroicon-o-envelope class="w-4 h-4" />
                                Email Order
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Items Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-left text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3 font-medium">Ingredient</th>
                            <th class="px-6 py-3 font-medium text-right">Current Stock</th>
                            <th class="px-6 py-3 font-medium text-right">Order Qty</th>
                            <th class="px-6 py-3 font-medium text-right">Unit Price</th>
                            <th class="px-6 py-3 font-medium text-right">Subtotal</th>
                            <th class="px-6 py-3 font-medium text-center">Lead Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($group['items'] as $item)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-6 py-3">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    @if ($item['sku'])
                                        <div class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="{{ $item['current_stock'] <= 0 ? 'text-danger-600' : 'text-warning-600' }}">
                                        {{ $item['current_stock'] }} {{ $item['unit'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-medium">{{ $item['needed'] }} {{ $item['unit'] }}</td>
                                <td class="px-6 py-3 text-right">@money($item['unit_price'])</td>
                                <td class="px-6 py-3 text-right font-medium">@money($item['subtotal'])</td>
                                <td class="px-6 py-3 text-center">
                                    @if ($item['lead_time_days'])
                                        {{ $item['lead_time_days'] }}d
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if ($supplierGroups->isNotEmpty())
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Grand Total</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                        @money($supplierGroups->sum('total'))
                    </span>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
