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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>
                    {{ $includeUpcoming ? 'Upcoming Orders Included' : 'Include Upcoming Orders' }}
                </button>
            </div>
        </div>

        @if($supplierGroups->isEmpty())
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto text-success-500" style="width: 48px; height: 48px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">All stocked up!</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No ingredients are currently below their low stock threshold.</p>
            </div>
        @endif

        {{-- Supplier Groups --}}
        @foreach($supplierGroups as $key => $group)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                {{-- Supplier Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-gray-400" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                            {{ $group['supplier']['name'] }}
                        </h3>
                        @if($group['supplier']['email'] || $group['supplier']['phone'])
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if($group['supplier']['email'])
                                    {{ $group['supplier']['email'] }}
                                @endif
                                @if($group['supplier']['email'] && $group['supplier']['phone']) &bull; @endif
                                @if($group['supplier']['phone'])
                                    {{ $group['supplier']['phone'] }}
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            ${{ number_format($group['total'], 2) }}
                        </span>
                        @if($group['supplier']['id'] && $group['supplier']['email'])
                            <button wire:click="sendPurchaseOrder({{ $group['supplier']['id'] }})"
                                wire:confirm="Send purchase order to {{ $group['supplier']['email'] }}?"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
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
                        @foreach($group['items'] as $item)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-6 py-3">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    @if($item['sku'])
                                        <div class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="{{ $item['current_stock'] <= 0 ? 'text-danger-600' : 'text-warning-600' }}">
                                        {{ $item['current_stock'] }} {{ $item['unit'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-medium">{{ $item['needed'] }} {{ $item['unit'] }}</td>
                                <td class="px-6 py-3 text-right">${{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-6 py-3 text-right font-medium">${{ number_format($item['subtotal'], 2) }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($item['lead_time_days'])
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

        @if($supplierGroups->isNotEmpty())
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Grand Total</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                        ${{ number_format($supplierGroups->sum('total'), 2) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
