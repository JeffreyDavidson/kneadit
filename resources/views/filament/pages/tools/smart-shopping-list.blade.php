@use(App\Support\StockQuantity)
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Controls --}}
        <div class="fi-section rounded-xl bg-(--kn-surface) p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="text-sm font-medium text-(--kn-ink-2)">Start Date</label>
                    <input
                        type="date"
                        wire:model.live="startDate"
                        wire:change="generateList"
                        class="focus:border-primary-500 focus:ring-primary-500 mt-1 block rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                    />
                </div>
                <div>
                    <label class="text-sm font-medium text-(--kn-ink-2)">End Date</label>
                    <input
                        type="date"
                        wire:model.live="endDate"
                        wire:change="generateList"
                        class="focus:border-primary-500 focus:ring-primary-500 mt-1 block rounded-lg border-(--kn-border) text-sm shadow-sm dark:bg-gray-800 dark:text-white"
                    />
                </div>
                <button
                    wire:click="toggleUpcoming"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium shadow-sm
                        {{
                            $includeUpcoming
                            ? 'bg-(--kn-honey) text-(--kn-on-honey) hover:bg-(--kn-honey-hover)'
                            : 'bg-(--kn-surface) text-(--kn-ink-2) ring-1 ring-gray-300 hover:bg-gray-50 dark:ring-gray-700'
                        }}"
                >
                    <x-heroicon-o-calendar-days class="h-4 w-4" />
                    {{ $includeUpcoming ? 'Upcoming Orders Included' : 'Include Upcoming Orders' }}
                </button>
            </div>
        </div>

        @if ($supplierGroups->isEmpty())
            <div class="fi-section rounded-xl bg-(--kn-surface) p-12 text-center shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <x-heroicon-o-check-circle class="text-success-500 mx-auto h-12 w-12" />
                <h3 class="mt-4 text-lg font-semibold text-(--kn-ink)">All stocked up!</h3>
                <p class="mt-2 text-sm text-(--kn-muted)">
                    No ingredients are currently below their low stock threshold.
                </p>
            </div>
        @endif

        {{-- Supplier Groups --}}
        @foreach ($supplierGroups as $key => $group)
            <div class="fi-section overflow-hidden rounded-xl bg-(--kn-surface) shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                {{-- Supplier Header --}}
                <div class="flex items-center justify-between border-b border-(--kn-border) bg-(--kn-surface-sunken) px-6 py-4">
                    <div>
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-(--kn-ink)">
                            <x-heroicon-o-truck class="h-5 w-5 text-(--kn-muted)" />
                            {{ $group['supplier']['name'] }}
                        </h3>
                        @if ($group['supplier']['email'] || $group['supplier']['phone'])
                            <p class="mt-1 text-sm text-(--kn-muted)">
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
                        <span class="text-lg font-bold text-(--kn-ink)">
                            @money($group['total'])
                        </span>
                        @if ($group['supplier']['id'] && $group['supplier']['email'])
                            <button
                                wire:click="sendPurchaseOrder({{ $group['supplier']['id'] }})"
                                wire:confirm="Send purchase order to {{ $group['supplier']['email'] }}?"
                                class="inline-flex items-center gap-2 rounded-lg bg-(--kn-honey) px-4 py-2 text-sm font-medium text-(--kn-on-honey) shadow-sm hover:bg-(--kn-honey-hover)"
                            >
                                <x-heroicon-o-envelope class="h-4 w-4" />
                                Email Order
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Items Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-(--kn-border) text-left text-(--kn-muted)">
                            <th class="px-6 py-3 font-medium">Ingredient</th>
                            <th class="px-6 py-3 text-right font-medium">Current Stock</th>
                            <th class="px-6 py-3 text-right font-medium">Order Qty</th>
                            <th class="px-6 py-3 text-right font-medium">Unit Price</th>
                            <th class="px-6 py-3 text-right font-medium">Subtotal</th>
                            <th class="px-6 py-3 text-center font-medium">Lead Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--kn-border)">
                        @foreach ($group['items'] as $item)
                            <tr class="text-(--kn-ink)">
                                <td class="px-6 py-3">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    @if ($item['sku'])
                                        <div class="text-xs text-(--kn-muted)">SKU: {{ $item['sku'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="{{ $item['current_stock'] <= 0 ? 'text-danger-600' : 'text-warning-600' }}">
                                        {{ StockQuantity::display($item['current_stock']) }} {{ $item['unit'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-medium">
                                    {{ $item['needed'] }} {{ $item['unit'] }}
                                </td>
                                <td class="px-6 py-3 text-right">@money($item['unit_price'])</td>
                                <td class="px-6 py-3 text-right font-medium">@money($item['subtotal'])</td>
                                <td class="px-6 py-3 text-center">
                                    @if ($item['lead_time_days'])
                                        {{ $item['lead_time_days'] }}d
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if ($supplierGroups->isNotEmpty())
            <div class="fi-section rounded-xl bg-(--kn-surface) p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-(--kn-ink)">Grand Total</span>
                    <span class="text-2xl font-bold text-(--kn-ink)">
                        @money($supplierGroups->sum('total'))
                    </span>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
